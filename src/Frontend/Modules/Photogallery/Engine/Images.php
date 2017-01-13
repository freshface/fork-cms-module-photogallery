<?php

namespace Frontend\Modules\Photogallery\Engine;

use Frontend\Core\Engine\Model as FrontendModel;

/**
 * In this file we store all generic functions that we will be using in the Photogallery module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Images
{
    public static function getAll($id)
    {
        $db = FrontendModel::get('database');

        $return =  (array) $db->getRecords(
           'SELECT i.id, i.data, c.name, c.description, i.filename
            FROM photogallery_album_images AS i
            INNER JOIN photogallery_photogallery_album_images_content AS c on c.image_id = i.id
            WHERE i.album_id = ? AND c.language = ? AND i.hidden = ? GROUP BY i.id ORDER BY i.sequence',
           array((int) $id, FRONTEND_LANGUAGE, 'N')
       );

        foreach ($return  as &$image) {
            $image['data'] = @unserialize($image['data']);
        }

        return  $return;
    }
}
