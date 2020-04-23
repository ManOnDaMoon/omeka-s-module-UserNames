<?php
namespace UserNames;

use Omeka\Module\AbstractModule;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Renderer\PhpRenderer;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;
use Omeka\Permissions\Acl;
use Omeka\Api\Exception\ValidationException;
use Omeka\Stdlib\ErrorStore;
use Omeka\Stdlib\Message;

class Module extends AbstractModule
{

    /**
     * Attach to Zend and Omeka specific listeners
     */
    public function attachListeners (
            SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.create.pre', [
            $this,
            'checkUserName'
        ]);
        
        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.create.post', [
            $this,
            'handleUserName'
        ]);

        $sharedEventManager->attach('Omeka\Api\Adapter\UserAdapter', 'api.update.post', [
            $this,
            'handleUserName'
        ]);

        $sharedEventManager->attach('Omeka\Api\Representation\UserRepresentation', 'rep.resource.json', [
            $this,
            'populateUserName'
        ]);

        $sharedEventManager->attach('Omeka\Form\UserForm', 'form.add_elements', [
            $this,
            'addUserNameField'
        ]);
        
        $sharedEventManager->attach('Omeka\Controller\Admin\User', 'view.show.after', [
            $this,
            'userViewShowAfter'
        ]);
        
        $sharedEventManager->attach('Omeka\Controller\Admin\User', 'view.details', [
            $this,
            'userViewDetails'
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
    public function getConfig ()
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
        $connectionService->exec('CREATE TABLE user_names (id INT NOT NULL, user_name VARCHAR(190) NOT NULL, UNIQUE INDEX UNIQ_10F1B21824A232CF (user_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
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
//         $api = $serviceLocator->get('Omeka\ApiManager');
//         $sites = $api->search('sites', [])->getContent();
//         /** @var \Omeka\Settings\SiteSettings $siteSettings */
//         $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

//         // v0.10 renamed site setting ID from 'restricted' to 'restrictedsites_restricted'
//         if (Comparator::lessThan($oldVersion, '0.10')) {
//             foreach ($sites as $site) {
//                 $siteSettings->setTargetId($site->id());
//                 if ($oldSetting = $siteSettings->get('restricted', null)) {
//                     $siteSettings->set('restrictedsites_restricted', $oldSetting);
//                     $siteSettings->delete('restricted');
//                 }
//             }
//         }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->exec('DROP TABLE `user_names`');
//         $settings = $serviceLocator->get('Omeka\Settings');
//         $settings->delete('restrictedsites_custom_email');

//         $api = $serviceLocator->get('Omeka\ApiManager');
//         $sites = $api->search('sites', [])->getContent();
//         $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

//         foreach ($sites as $site) {
//             $siteSettings->setTargetId($site->id());
//             $siteSettings->delete('restrictedsites_restricted');
//         }
    }

    /**
     * Get this module's configuration form.
     *
     * @param PhpRenderer $renderer
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
//         $formElementManager = $this->getServiceLocator()->get('FormElementManager');
//         $form = $formElementManager->get(ConfigForm::class, []);
//         return $renderer->formCollection($form, false);
    }

    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm(AbstractController $controller)
    {
//         $params = $controller->params()->fromPost();
//         if (isset($params['restrictedsites_custom_email'])) {
//             $customEmailSetting = $params['restrictedsites_custom_email'];
//         }

//         $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
//         $globalSettings->set('restrictedsites_custom_email', $customEmailSetting);
    }

    /**
     * Called on module application bootstrap, this adds the required ACL level
     * authorization for anybody to use the sitelogin controller
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::onBootstrap()
     */
    public function onBootstrap (MvcEvent $event)
    {
        //TODO : Fix authorizations for other users than admin e.g. editor cannot edit its own username currently

        parent::onBootstrap($event);

        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, [
            'UserNames\Controller\Login'
        ], null);
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
        $userNames = $api->search('usernames', ['id' => $jsonLd['o:id']])->getContent();
        if (!empty($userNames[0])) {
            $jsonLd['o-module-usernames:username'] = $userNames[0]->userName();
            $event->setParam('jsonLd', $jsonLd);
        }
    }

    public function handleUserName(EventInterface $event)
    {
        $request = $event->getParam('request');
        $response = $event->getParam('response');
        $data = $response->getContent();

        $api = $this->getServiceLocator()->get('Omeka\ApiManager');

        $userName['id'] = $data->getId();
        $userName['o-module-usernames:username'] = $request->getContent()['o-module-usernames:username'];

        $searchResponse = $api->search('usernames', ['id' => $userName['id']]);
        if (empty($searchResponse->getContent())) {
            //create
            $response = $api->create('usernames', $userName);
        } else {
            // update
            $response = $api->update('usernames', $userName['id'], $userName);
        }
    }
    
    public function checkUserName(EventInterface $event)
    {
        /** @var \Omeka\Api\Adapter\UserAdapter $userAdapter */
        $userAdapter = $event->getTarget();
        $request = $event->getParam('request');
        
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        
        $userName['o-module-usernames:username'] = $request->getContent()['o-module-usernames:username'];
        
        $searchResponse = $api->search('usernames', ['userName' => $userName['o-module-usernames:username']]);
        if (!empty($searchResponse->getContent())) {
            // Username exists. Warn.
            $errorStore = new ErrorStore();
            $errorStore->addError('o-module-usernames:username', new Message(
                'The user name %s is already taken.', // @translate
                $userName['o-module-usernames:username']
                ));
            $validationException = new ValidationException();
            $validationException->setErrorStore($errorStore);
            throw $validationException;
        }
    }
    
    public function renderUserName($userId, PhpRenderer $phpRenderer, $partial)
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $searchResponse = $api->search('usernames', [
            'id' => $userId
        ]);
        if (! empty($userName = $searchResponse->getContent())) {
            echo $phpRenderer->partial($partial, [
                'username' => $userName[0]->userName()
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
}