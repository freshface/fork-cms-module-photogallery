<?php

namespace Backend\Modules\Photogallery\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model;
use Backend\Modules\Photogallery\Engine\Model as BackendPhotogalleryModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Delete extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendPhotogalleryModel::exists($this->id)) {
            parent::execute();
            $this->record = (array) BackendPhotogalleryModel::get($this->id);

            // delete extra_ids
            foreach ($this->record['content'] as $row) {
                Model::deleteExtraById($row['slideshow_extra_id'], true);
                Model::deleteExtraById($row['lightbox_extra_id'], true);
            }

            BackendPhotogalleryModel::delete($this->id);

            Model::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                Model::createURLForAction('Index') . '&report=deleted'
            );
        } else {
            $this->redirect(Model::createURLForAction('Index') . '&error=non-existing');
        }
    }
}
