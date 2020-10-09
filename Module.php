<?php
namespace UserNames;

use Omeka\Module\AbstractModule;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\EventInterface;
use Omeka\Permissions\Acl;
use Omeka\Api\Exception\ValidationException;
use Omeka\Stdlib\ErrorStore;
use Omeka\Stdlib\Message;
use Laminas\Validator\Regex;
use UserNames\Form\ConfigForm;
use Omeka\Settings\Settings;
use Composer\Semver\Comparator;

class Module extends AbstractModule
{
    const DEFAULT_USER_MIN_LENGTH = 1;
    const DEFAULT_USER_MAX_LENGTH = 30;
    const MAX_SQL_USERNAME_LENGTH = 190;

    protected $errorStore;

    /**
     * Attach to Laminas and Omeka specific listeners
     */
    public function attachListeners(
            SharedEventManagerInterface $sharedEventManager)
    {
        // Validate username constraints before user creation
        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.create.pre', [
            $this,
            'validateUserName',
        ]);

        // Handle username creation, update and deletion
        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.create.post', [
            $this,
            'handleUserName',
        ]);

        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.update.post', [
            $this,
            'handleUserName',
        ]);

        // Populate user representation with username especially for user add/edit forms
        $sharedEventManager->attach('Omeka\Api\Representation\UserRepresentation', 'rep.resource.json', [
            $this,
            'populateUserName',
        ]);

        // Add username field to user add/edit form
        $sharedEventManager->attach('Omeka\Form\UserForm', 'form.add_elements', [
            $this,
            'addUserNameField',
        ]);

        // Show username on admin user view
        $sharedEventManager->attach('Omeka\Controller\Admin\User', 'view.show.after', [
            $this,
            'userViewShowAfter',
        ]);

        // Show username on admin user details view
        // TODO: add username to user table? Not sure if easy.
        $sharedEventManager->attach('Omeka\Controller\Admin\User', 'view.details', [
            $this,
            'userViewDetails',
        ]);

        // Attach to RestrictedSites module form event
        $sharedEventManager->attach('RestrictedSites\Form\SiteLoginForm', 'form.add_elements', [
            $this,
            'modifySiteLoginForm',
        ]);
    }

    /**
     * Include the configuration array containing the sitelogin controller, the
     * sitelogin controller factory and the sitelogin route
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::getConfig()
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Install this module.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connectionService = $serviceLocator->get('Omeka\Connection');
        //V0.2
        //$connectionService->exec('CREATE TABLE user_names (user_id INT NOT NULL, user_name VARCHAR(190) NOT NULL, UNIQUE INDEX UNIQ_10F1B21824A232CF (user_name), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
        //$connectionService->exev('ALTER TABLE user_names ADD CONSTRAINT FK_10F1B218A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;');

        $connectionService->exec('CREATE TABLE user_names (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_name VARCHAR(190) NOT NULL, UNIQUE INDEX UNIQ_10F1B21824A232CF (user_name), UNIQUE INDEX UNIQ_10F1B218A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
        $connectionService->exec('ALTER TABLE user_names ADD CONSTRAINT FK_10F1B218A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;');

        $globalSettings = $serviceLocator->get('Omeka\Settings');
        $globalSettings->set('usernames_min_length', self::DEFAULT_USER_MIN_LENGTH);
        $globalSettings->set('usernames_max_length', self::DEFAULT_USER_MAX_LENGTH);
    }

    /**
     * Upgrade this module.
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $connectionService = $serviceLocator->get('Omeka\Connection');

        if (Comparator::lessThan($oldVersion, '0.2')) {
            //V0.2
            // Rename id column to user_id.
            $connectionService->exec('ALTER TABLE user_names CHANGE id user_id INT;');
            $connectionService->exec('ALTER TABLE user_names ADD UNIQUE INDEX UNIQ_10F1B218A76ED395 (user_id);');
            $connectionService->exec('ALTER TABLE user_names DROP PRIMARY KEY;');
            $connectionService->exec('ALTER TABLE user_names ADD COLUMN id INT PRIMARY KEY AUTO_INCREMENT NOT NULL;');
            $connectionService->exec('ALTER TABLE user_names ADD CONSTRAINT FK_10F1B218A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;');
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->exec('DROP TABLE `user_names`');

        /** @var Settings $globalSettings */
        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $globalSettings->delete('usernames_min_length');
        $globalSettings->delete('usernames_max_length');
    }

    /**
     * Get this module's configuration form.
     *
     * @param PhpRenderer $renderer
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class, []);
        return $renderer->formCollection($form, false);
    }

    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $params = $controller->params()->fromPost();
        if (isset($params['usernames_min_length'])) {
            $userNamesMinLength = $params['usernames_min_length'];
        } else {
            $this->addError('usernames_min_length', new Message(
                'Minimum length cannot be empty.' // @translate
                ));
        }

        if (isset($params['usernames_max_length'])) {
            $userNamesMaxLength = $params['usernames_max_length'];
        } else {
            $this->addError('usernames_max_length', new Message(
                'Maximum length cannot be empty.' // @translate
                ));
        }

        if ($userNamesMaxLength < $userNamesMinLength ||
            $userNamesMinLength < 1 ||
            $userNamesMaxLength > self::MAX_SQL_USERNAME_LENGTH) {
            $this->addError('usernames_max_length', new Message(
                    'Max and min length out of bounds. Maximum length cannot be over 190.' // @translate
                    ));
        }

        if ($this->errorStore && $this->errorStore->hasErrors()) {
            return false; // Omeka S does not provide a way to explicit error here yet.
        }

        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $globalSettings->set('usernames_min_length', $userNamesMinLength);
        $globalSettings->set('usernames_max_length', $userNamesMaxLength);
    }

    /**
     * Called on module application bootstrap, this adds the required ACL level
     * authorization for anybody to use the sitelogin controller
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::onBootstrap()
     */
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, [
            'UserNames\Controller\Login',
        ], null);

        // Add autorizations to UserNameAdapter for all roles
        $acl->allow(
            'editor',
            'UserNames\Api\Adapter\UserNameAdapter',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'reviewer',
            'UserNames\Api\Adapter\UserNameAdapter',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'researcher',
            'UserNames\Api\Adapter\UserNameAdapter',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'author',
            'UserNames\Api\Adapter\UserNameAdapter',
            ['read', 'update', 'search']
            );

        $acl->allow(
            'editor',
            'UserNames\Entity\UserNames',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'reviewer',
            'UserNames\Entity\UserNames',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'researcher',
            'UserNames\Entity\UserNames',
            ['read', 'update', 'search']
            );
        $acl->allow(
            'author',
            'UserNames\Entity\UserNames',
            ['read', 'update', 'search']
            );
    }

    public function addUserNameField(EventInterface $event)
    {
        /** @var \Omeka\Form\UserForm $form */
        $form = $event->getTarget();

        $fieldset = $form->get('user-information');

        $fieldset->add([
            'name' => 'o-module-usernames:username',
            'type' => 'Text',
            'options' => [
                'label' => 'User name', // @translate
            ],
            'attributes' => [
                'id' => 'username',
                'required' => true,
            ],
        ]);

        return;
    }

    public function populateUserName(EventInterface $event)
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $jsonLd = $event->getParam('jsonLd');
        $userNames = $api->search('usernames', ['user' => $jsonLd['o:id']], ['limit' => 1])->getContent();//FIXME
        if (!empty($userNames[0])) {
            $jsonLd['o-module-usernames:username'] = $userNames[0]->userName();
            $event->setParam('jsonLd', $jsonLd);
        }
    }

    public function handleUserName(EventInterface $event)
    {
        $request = $event->getParam('request');
        $operation = $request->getOperation();
        /** @var \Omeka\Api\Manager $api */
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');

        if ($operation == 'update' || $operation == 'create') {
            $response = $event->getParam('response');
            $data = $response->getContent();

            $userName['user'] = $data->getId();
            if ($content = $request->getContent())
            {
                $userName['o-module-usernames:username'] = $content['o-module-usernames:username'];

                $searchResponse = $api->search('usernames', ['user' => $userName['user']]);
                if (empty($searchResponse->getContent())) {
                    //create
                    $response = $api->create('usernames', $userName);
                } else {
                    // update
                    $response = $api->update('usernames', $searchResponse->getContent()[0]->id(), $userName);
                }
            }
        }
    }

    public function addError($property, Message $message)
    {
        if (!$this->errorStore) {
            $this->errorStore = new ErrorStore();
        }
        $this->errorStore->addError($property, $message);
    }

    public function throwValidationExceptionIfErrors()
    {
        if ($this->errorStore && $this->errorStore->hasErrors()) {
            $validationException = new ValidationException();
            $validationException->setErrorStore($this->errorStore);
            throw $validationException;
        }
    }

    public function validateUserName(EventInterface $event)
    {
        // TODO : Most of this code duplicates UserNameAdapter::validateEntity(). Needs refactoring.
        $request = $event->getParam('request');
        $userNameProperty = 'o-module-usernames:username';
        $userName = $request->getContent()[$userNameProperty];

        // Empty username
        if (!$userName) {
            $this->addError($userNameProperty, new Message(
                'The user name cannot be empty.' // @translate
                ));
        }

        // Username length
        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $userNamesMinLength = $globalSettings->get('usernames_min_length');
        $userNamesMaxLength = $globalSettings->get('usernames_max_length');
        if (strlen($userName) < $userNamesMinLength
            || strlen($userName) > $userNamesMaxLength) {
            $this->addError($userNameProperty, new Message(
                'User name must be between %1$s and %2$s characters.', // @translate
                $userNamesMinLength, $userNamesMaxLength
                ));
        }

        // Invalid username
        $validator = new Regex('#^[a-zA-Z0-9.*@+!\-_%\#\^&$]*$#u');
        if (!$validator->isValid($userName)) {
            $this->addError($userNameProperty, new Message(
                'Whitespace is not allowed. Only these special characters may be used: %s', // @translate
                ' + ! @ # $ % ^ & * . - _'
                ));
        }

        // Existing username
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $searchResponse = $api->search('usernames', ['userName' => $userName]);
        if (!empty($searchResponse->getContent())) {
            // Username exists. Warn.
            $this->addError($userNameProperty, new Message(
                'The user name %s is already taken.', // @translate
                $userName
                ));
        }

        $this->throwValidationExceptionIfErrors();
    }

    public function renderUserName($userId, PhpRenderer $phpRenderer, $partial)
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $searchResponse = $api->search('usernames', [
            'user' => $userId,
        ], ['limit' => 1]);
        if (! empty($userName = $searchResponse->getContent())) {
            echo $phpRenderer->partial($partial, [
                'username' => $userName[0]->userName(),
            ]);
        }
    }

    public function userViewShowAfter(EventInterface $event)
    {
        $userId = $event->getTarget()->vars()->user->id();
        $this->renderUserName($userId, $event->getTarget(), 'common/admin/username-show');
    }
    public function userViewDetails(EventInterface $event)
    {
        $userId = $event->getTarget()->vars()->resource->id();
        $this->renderUserName($userId, $event->getTarget(), 'common/admin/username-detail');
    }

    public function modifySiteLoginForm(EventInterface $event)
    {
        /** @var \RestrictedSites\Form\SiteLoginForm $form */
        $form = $event->getTarget();

        $form->add([
            'name' => 'email',
            'type' => 'text',
            'options' => [
                'label' => 'User name or email', // @translate
            ],
            'attributes' => [
                'required' => true,
                'id' => 'email',
            ],
        ], [ 'priority' => 1]);
    }
}
