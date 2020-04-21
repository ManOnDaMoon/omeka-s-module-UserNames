<?php
namespace UserNames;

use Omeka\Module\AbstractModule;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Renderer\PhpRenderer;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\SharedEventManagerInterface;

class Module extends AbstractModule
{

    /**
     * Attach to Zend and Omeka specific listeners
     */
    public function attachListeners (
            SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('Omeka\Form\LoginForm', 'form.add_elements', [
            $this,
            'modifyLoginForm'
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
        $connectionService->exec('CREATE TABLE user_names (user_id INT NOT NULL, user_name VARCHAR(190) NOT NULL, UNIQUE INDEX UNIQ_10F1B21824A232CF (user_name), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
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
        parent::onBootstrap($event);

        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, [
            'UserNames\Controller\Login'
        ], null);
    }

    public function modifyLoginForm(EventInterface $event)
    {
        /** @var \Omeka\Form\UserForm $form */
        $form = $event->getTarget();

        $form->get('email')->setOptions([
            'label' => 'Username or email' // @translate
        ]);

        return;
    }
}