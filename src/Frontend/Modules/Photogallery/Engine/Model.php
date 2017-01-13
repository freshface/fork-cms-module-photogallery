<?php

namespace Frontend\Modules\Photogallery\Engine;

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Language;
use Frontend\Core\Engine\Navigation;
use Frontend\Modules\Photogallery\Engine\Images as FrontendPhotogalleryImagesModel;

/**
 * In this file we store all generic functions that we will be using in the Photogallery module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Model
{
    public static function get($id)
    {
        $item = (array) FrontendModel::getContainer()->get('database')->getRecord(
           'SELECT i.id, c.name, c.description
            FROM photogallery AS i
            JOIN photogallery_album_content AS c on c.album_id = i.id
            WHERE i.status = ? AND i.publish_on <= ? AND i.id = ? AND c.language = ? AND i.hidden = ?',
           array(
              'active',
              FrontendModel::getUTCDate('Y-m-d H:i') . ':00',
              (int) $id,
               FRONTEND_LANGUAGE,
               'N'
           )
       );

       // no results?
       if (empty($item)) {
           return array();
       }

       // init var
       $item['images'] = FrontendPhotogalleryImagesModel::getAll($item['id']);

       // return
       return $item;
    }
}
