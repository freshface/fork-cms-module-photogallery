<?php

namespace Backend\Modules\Photogallery\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Photogallery\Engine\Images as BackendPhotogalleryImagesModel;

/**
 * Alters the sequence of Photogallery articles
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class SequenceImages extends AjaxAction
{
    public function execute()
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            $item['id'] = $id;
            $item['sequence'] = $i + 1;

            // update sequence
            if (BackendPhotogalleryImagesModel::exists($id)) {
                BackendPhotogalleryImagesModel::update($item);
            }
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
