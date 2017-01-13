<?php

namespace Backend\Modules\Photogallery\Installer;

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the Photogallery module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Installer extends ModuleInstaller
{
    public function install()
    {
        // import the sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // install the module in the database
        $this->addModule('Photogallery');

        // install the locale, this is set here beceause we need the module for this
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        $this->setModuleRights(1, 'Photogallery');

        $this->setActionRights(1, 'Photogallery', 'Add');
        $this->setActionRights(1, 'Photogallery', 'AddImages');
        $this->setActionRights(1, 'Photogallery', 'Categories');
        $this->setActionRights(1, 'Photogallery', 'Delete');
        $this->setActionRights(1, 'Photogallery', 'DeleteImage');
        $this->setActionRights(1, 'Photogallery', 'Edit');
        $this->setActionRights(1, 'Photogallery', 'Index');

        $this->setActionRights(1, 'Photogallery', 'Sequence');
        $this->setActionRights(1, 'Photogallery', 'SequenceImages');
        $this->setActionRights(1, 'Photogallery', 'UploadImages');
        $this->setActionRights(1, 'Photogallery', 'EditImage');

        $this->setActionRights(1, 'Photogallery', 'Settings');
        $this->setActionRights(1, 'Photogallery', 'UploadImage');

        //$this->makeSearchable('Photogallery');

        // add extra's
        //$subnameID = $this->insertExtra('Photogallery', 'block', 'Photogallery', null, null, 'N', 1000);
        //$this->insertExtra('Photogallery', 'block', 'AlbumDetail', 'Detail', null, 'N', 1001);

        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $this->setNavigation($navigationModulesId, 'Photogallery', 'photogallery/index', array('photogallery/add', 'photogallery/edit', 'photogallery/index', 'photogallery/add_images', 'photogallery/edit_image'), 1);

         // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'Photogallery', 'photogallery/settings');
    }
}
