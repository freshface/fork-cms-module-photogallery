<?php

namespace Backend\Modules\Photogallery\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model;
use Backend\Modules\Photogallery\Engine\Images as BackendPhotogalleryImagesModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class DeleteImage extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendPhotogalleryImagesModel::exists($this->id)) {
            parent::execute();
            $this->record = (array) BackendPhotogalleryImagesModel::get($this->id);
            Model::deleteThumbnails(FRONTEND_FILES_PATH . '/' . $this->getModule() . '/images',  $this->record['filename']);

            BackendPhotogalleryImagesModel::delete($this->id);

            Model::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                Model::createURLForAction('Edit') . '&report=deleted&id=' . $this->record['album_id']  .'#tabImages'
            );
        } else {
            $this->redirect(Model::createURLForAction('Edit') . '&error=non-existing');
        }
    }
}
