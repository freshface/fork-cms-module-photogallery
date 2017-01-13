<?php

namespace Backend\Modules\Photogallery\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Authentication;
use Backend\Core\Engine\DataGridDB;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\Photogallery\Engine\Model as BackendPhotogalleryModel;
use Backend\Core\Engine\Form;

/**
 * This is the index-action (default), it will display the overview of Photogallery posts
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Index extends ActionIndex
{
    private $filter = [];

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->setFilter();
        $this->loadForm();

        $this->loadDataGridPhotogallery();
        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGridPhotogallery()
    {
        $query = 'SELECT i.id, c.name,  i.hidden
         FROM photogallery AS i
         INNER JOIN photogallery_album_content as c  on i.id = c.album_id';


        $query .= ' WHERE 1';

        $parameters = array();
        $query .= ' AND c.language = ?';
        $parameters[] = Language::getWorkingLanguage();

        $query .= ' AND i.status = ?';
        $parameters[] = 'active';

        if ($this->filter['value']) {
            $query .= ' AND c.name LIKE ?';
            $parameters[] = '%' . $this->filter['value'] . '%';
        }

        $query .= 'GROUP BY i.id ORDER BY sequence DESC';

        $this->dataGridPhotogallery = new DataGridDB(
            $query,
            $parameters
        );


        //\Spoon::dump($this->dataGridPhotogallery->getURL());
        $this->dataGridPhotogallery->setURL($this->dataGridPhotogallery->getURL() . '&' . http_build_query($this->filter));

        //$this->dataGridPhotogallery->enableSequenceByDragAndDrop();

        $this->dataGridPhotogallery->setPagingLimit(50);

        $this->dataGridPhotogallery->setColumnAttributes(
            'name', array('class' => 'title')
        );

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGridPhotogallery->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGridPhotogallery->setColumnURL(
                'name', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }
    }



    /**
     * Load the form
     */
    private function loadForm()
    {
        $this->frm = new Form('filter', Model::createURLForAction(), 'get');


        $this->frm->addText('value', $this->filter['value']);


        // manually parse fields
        $this->frm->parse($this->tpl);
    }


    /**
     * Sets the filter based on the $_GET array.
     */
    private function setFilter()
    {
        $this->filter['value'] = $this->getParameter('value') == null ? '' : $this->getParameter('value');
    }


    /**
     * Parse the page
     */
    protected function parse()
    {
        // parse the dataGrid if there are results
        $this->tpl->assign('dataGridPhotogallery', (string) $this->dataGridPhotogallery->getContent());
    }
}
