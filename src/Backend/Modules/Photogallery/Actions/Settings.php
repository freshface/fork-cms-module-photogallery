<?php

namespace Backend\Modules\Photogallery\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Language\Language as BL;

/**
 * This is the settings-action, it will display a form to set general Photogallery settings
 *
 * @author Tijs Verkoyen <tijs@sumocoders.be>
 * @author Dave Lens <dave.lens@netlash.com>
 * @author Jelmer Snoeck <jelmer@siphoc.com>
 */
class Settings extends BackendBaseActionEdit
{
    /**
     * Is the user a god user?
     *
     * @var bool
     */
    protected $isGod = false;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    /**
     * Loads the settings form
     */
    private function loadForm()
    {
        $this->isGod = BackendAuthentication::getUser()->isGod();

        $this->frm = new BackendForm('settings');
    }

    /**
     * Parse the form
     */
    protected function parse()
    {
        parent::parse();

        // parse additional variables
        $this->tpl->assign('isGod', $this->isGod);
    }

    /**
     * Validates the settings form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            if ($this->frm->isCorrect()) {

            

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_saved_settings');

                // redirect to the settings page
                $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
            }
        }
    }
}
