<?php

namespace Backend\Modules\Photogallery\Engine;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;
use Backend\Modules\Photogallery\Engine\Images as BackendPhotogalleryImagesModel;

/**
 * In this file we store all generic functions that we will be using in the Photogallery module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Model
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, c.name,  i.sequence, i.hidden
         FROM photogallery AS i
         INNER JOIN photogallery_album_content as c  on i.id = c.album_id
         WHERE c.language = ? AND i.status = ? ORDER BY sequence DESC';

       /**
       * Get the maximum Team sequence.
       *
       * @return int
       */
      public static function getMaximumSequence()
      {
          return (int) BackendModel::get('database')->getVar(
              'SELECT MAX(i.sequence)
               FROM photogallery AS i'
          );
      }

     /**
      * Retrieve the unique URL for an item
      *
      * @param string $URL The URL to base on.
      * @param int    $id  The id of the item to ignore.
      * @return string
      */
     public static function getURL($URL, $language, $id = null)
     {
         $URL = (string) $URL;

         // get db
         $db = BackendModel::getContainer()->get('database');

         // new item
         if ($id === null) {
             // already exists
             if ((bool) $db->getVar(
                 'SELECT 1
                  FROM photogallery AS i
                  INNER JOIN photogallery_album_content AS m ON i.id = m.album_id
                  WHERE m.language = ? AND m.url = ?
                  LIMIT 1',
                 array($language, $URL)
             )
             ) {
                 $URL = BackendModel::addNumber($URL);

                 return self::getURL($URL, $language);
             }
         } else {
             // current category should be excluded
             if ((bool) $db->getVar(
                 'SELECT 1
                  FROM photogallery AS i
                  INNER JOIN photogallery_album_content AS m ON i.id = m.album_id
                  WHERE m.language = ? AND m.url = ? AND i.id != ?
                  LIMIT 1',
                 array($language, $URL, $id)
             )
             ) {
                 $URL = BackendModel::addNumber($URL);

                 return self::getURL($URL, $language, $id);
             }
         }

         return $URL;
     }

    /**
     * Delete a certain item
     *
     * @param int $id
     */
    public static function delete($id)
    {
        BackendModel::get('database')->delete('photogallery', 'id = ?', (int) $id);
        BackendModel::get('database')->delete('photogallery_album_content', 'album_id = ?', (int) $id);

        $images = (array) BackendPhotogalleryImagesModel::getAll((int) $id);
        foreach ($images as $image) {
            BackendModel::deleteThumbnails(FRONTEND_FILES_PATH . '/' . BackendModel::get('url')->getModule() . '/uploaded_images',  $image['filename']);
        }

        BackendModel::get('database')->execute('DELETE c FROM photogallery_photogallery_album_images_content c INNER JOIN photogallery_album_images i ON c.image_id = i.id WHERE i.album_id = ?', array((int) $id));
        BackendModel::get('database')->delete('photogallery_album_images', 'album_id = ?', (int) $id);
    }

    /**
     * Checks if a certain item exists
     *
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        return (bool) BackendModel::get('database')->getVar(
            'SELECT 1
             FROM photogallery AS i
             WHERE i.id = ?
             LIMIT 1',
            array((int) $id)
        );
    }

    /**
     * Fetches a certain item
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        $db = BackendModel::get('database');

        $return =  (array) $db->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.publish_on) as publish_on
             FROM photogallery AS i
             WHERE i.id = ?',
            array((int) $id)
        );

        // data found
        $return['content'] = (array) $db->getRecords(
            'SELECT i.* FROM photogallery_album_content AS i
            WHERE i.album_id = ?',
            array((int) $id), 'language');

        return  $return;
    }





    /**
     * Insert an item in the database
     *
     * @param array $item
     * @return int
     */
    public static function insert(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();

        return (int) BackendModel::get('database')->insert('photogallery', $item);
    }

    public static function insertContent(array $content, $parentData)
    {
        foreach ($content as &$item) {
            if ($parentData['slideshow'] == 'Y') {
                $data = [
                    'id' => $item['album_id'],
                    'language' => $item['language'],
                    'extra_label' => 'Slideshow: ' . $item['name'],
                    'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $item['album_id'],
                ];

                $item['slideshow_extra_id'] = BackendModel::insertExtra(
                    'widget',
                    'Photogallery',
                    'Slideshow',
                    'Slideshow',
                    $data
                );
            }

            if ($parentData['lightbox'] == 'Y') {
                $data = [
                    'id' => $item['album_id'],
                    'language' => $item['language'],
                    'extra_label' => 'Lightbox: ' . $item['name'],
                    'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $item['album_id'],
                ];

                $item['lightbox_extra_id'] = BackendModel::insertExtra(
                    'widget',
                    'Photogallery',
                    'Lightbox',
                    'Lightbox',
                    $data
                );
            }

            BackendModel::get('database')->insert('photogallery_album_content', $item);
        }
    }

    /**
     * Updates an item
     *
     * @param array $item
     */
    public static function update(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();

        BackendModel::get('database')->update(
            'photogallery', $item, 'id = ?', (int) $item['id']
        );
    }

    public static function updateContent(array $content, $id, $parentData)
    {
        $db = BackendModel::get('database');



        foreach ($content as $language => $row) {
            // slideshow
            if ($parentData['slideshow'] == 'Y' && $row['slideshow_extra_id']) {
                // update
                $data = [
                    'id' => $row['album_id'],
                    'language' => $row['language'],
                    'extra_label' => 'Slideshow: ' . $row['name'],
                    'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $row['album_id'],
                ];

                BackendModel::updateExtra($row['slideshow_extra_id'], 'data', $data);
            } elseif ($parentData['slideshow'] == 'Y' && !$row['slideshow_extra_id']) {
                // insert
                    $data = [
                        'id' => $row['album_id'],
                        'language' => $row['language'],
                        'extra_label' => 'Slideshow: ' . $row['name'],
                        'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $row['album_id'],
                    ];

                $row['slideshow_extra_id'] = BackendModel::insertExtra(
                        'widget',
                        'Photogallery',
                        'Slideshow',
                        'Slideshow',
                        $data
                    );
            } elseif ($parentData['slideshow'] == 'N' && $row['slideshow_extra_id']) {
                // remove

                    BackendModel::deleteExtraById($row['slideshow_extra_id'], true);
                $row['slideshow_extra_id'] = null;
            }

                // lightbox
                if ($parentData['lightbox'] == 'Y' && $row['lightbox_extra_id']) {
                    // update
                    $data = [
                        'id' => $row['album_id'],
                        'language' => $row['language'],
                        'extra_label' => 'Lightbox: ' . $row['name'],
                        'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $row['album_id'],
                    ];

                    BackendModel::updateExtra($row['lightbox_extra_id'], 'data', $data);
                } elseif ($parentData['lightbox'] == 'Y' && !$row['lightbox_extra_id']) {
                    // insert
                    $data = [
                        'id' => $row['album_id'],
                        'language' => $row['language'],
                        'extra_label' => 'Lightbox: ' . $row['name'],
                        'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $row['album_id'],
                    ];

                    $row['lightbox_extra_id'] = BackendModel::insertExtra(
                        'widget',
                        'Photogallery',
                        'Lightbox',
                        'Lightbox',
                        $data
                    );
                } elseif ($parentData['lightbox'] == 'N' && $row['lightbox_extra_id']) {
                    // remove
                    BackendModel::deleteExtraById($row['lightbox_extra_id'], true);
                    $row['lightbox_extra_id'] = null;
                }

            $db->update('photogallery_album_content', $row, 'album_id = ? AND language = ?', array($id, $language));
        }
    }
}
