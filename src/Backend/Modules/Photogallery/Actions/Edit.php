<?php

namespace Backend\Modules\Photogallery\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\Photogallery\Engine\Model as BackendPhotogalleryModel;
use Backend\Modules\Photogallery\Engine\Images as BackendPhotogalleryImagesModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;

use Backend\Modules\SiteHelpers\Engine\Helper as SiteHelpersHelper;
use Backend\Modules\SiteHelpers\Engine\Model as SiteHelpersModel;
use Backend\Modules\SiteHelpers\Engine\Assets as SiteHelpersAssets;
use Common\Uri as CommonUri;

use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Symfony\Component\Filesystem\Filesystem;
use Backend\Core\Engine\Authentication;

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Edit extends ActionEdit
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->languages = SiteHelpersHelper::getActiveLanguages();
        //SiteHelpersAssets::addSelect2($this->header);

        $this->loadData();
        $this->loadImagesDataGrid();
        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    private function loadImagesDataGrid()
    {

        // create dataGrid
        $this->dataGrid = new BackendDataGridDB(BackendPhotogalleryImagesModel::QRY_DATAGRID_BROWSE_IMAGES_FOR_PROJECT, array($this->record['id']));
        $this->dataGrid->setMassActionCheckboxes('checkbox', '[id]');

        // set drag and drop
        $this->dataGrid->enableSequenceByDragAndDrop();

        // disable paging
        $this->dataGrid->setPaging(false);

        // set colum URLs
        //$this->dataGrid->setColumnURL('preview', Model::createURLForAction('edit_image') . '&amp;id=[id]&amp;album_id=' . $this->id);

        // set colums hidden
        // $this->dataGrid->setColumnsHidden(array('category_id', 'sequence'));

        // add edit column
        $this->dataGrid->addColumn('edit', null, Language::lbl('Edit'), Model::createURLForAction('edit_image') . '&amp;id=[id]&amp;album_id=' . $this->id, Language::lbl('Edit'));

        $this->dataGrid->addColumn('delete', null, Language::lbl('Delete'), Model::createURLForAction('DeleteImage') . '&amp;id=[id]', Language::lbl('Delete'));


        $this->dataGrid->addColumn('preview', \SpoonFilter::ucfirst(Language::lbl('Preview')));
        $this->dataGrid->setColumnFunction(array('Backend\Modules\SiteHelpers\Engine\Helper', 'getPreviewHTML'), array('[filename]', 'Photogallery', 'images', '200x'), 'preview', true);

        // make sure the column with the handler is the first one
        $this->dataGrid->setColumnsSequence('dragAndDropHandle', 'checkbox', 'preview', 'filename', 'delete');

        // Hidden
        $this->dataGrid->setColumnsHidden(array('filename', 'checkbox'));

        // add a class on the handler column, so JS knows this is just a handler
        $this->dataGrid->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));

        // our JS needs to know an id, so we can send the new order
        $this->dataGrid->setRowAttributes(array('id' => '[id]'));

        $this->dataGrid->setAttributes(array('data-action' => "SequenceImages"));

        // add mass action dropdown
        $ddmMassAction = new \SpoonFormDropdown('action', array('-' =>  Language::getLabel('Choose'), 'delete' => Language::getLabel('Delete')), '-');
        $ddmMassAction->setAttribute('id', 'actionDelete');
        //$this->dataGrid->setMassAction($ddmMassAction);
        //$this->frm->add($ddmMassAction);

        $this->tpl->assign('imagesDataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
    }

    /**
     * Load the item data
     */
    protected function loadData()
    {
        $this->id = $this->getParameter('id', 'int', null);
        if ($this->id == null || !BackendPhotogalleryModel::exists($this->id)) {
            $this->redirect(
                Model::createURLForAction('Index') . '&error=non-existing'
            );
        }

        $this->record = BackendPhotogalleryModel::get($this->id);
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        // create form
        $this->frm = new Form('edit');

        $this->frm->addDate('publish_on_date', $this->record['publish_on']);
        $this->frm->addTime('publish_on_time', date('H:i', $this->record['publish_on']));

        // set hidden values
        $rbtHiddenValues[] = array('label' => Language::lbl('Hidden', $this->URL->getModule()), 'value' => 'Y');
        $rbtHiddenValues[] = array('label' => Language::lbl('Published'), 'value' => 'N');

        $this->frm->addCheckbox('slideshow', $this->record['slideshow'] == 'Y');
        $this->frm->addCheckbox('lightbox', $this->record['lightbox'] == 'Y');

        $this->frm->addRadiobutton('hidden', $rbtHiddenValues, $this->record['hidden']);

        foreach ($this->languages as &$language) {
            $field = $this->frm->addText('name_'. $language['abbreviation'], isset($this->record['content'][$language['abbreviation']]['name']) ? $this->record['content'][$language['abbreviation']]['name'] : '', null, 'form-control title', 'form-control danger title');
            $language['name_field'] = $field->parse();

            $field = $this->frm->addEditor('description_'. $language['abbreviation'], isset($this->record['content'][$language['abbreviation']]['description']) ? $this->record['content'][$language['abbreviation']]['description'] : '');
            $language['description_field'] = $field->parse();
        }
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();

        $this->tpl->assign('languages', $this->languages);
        $this->tpl->assign('record', $this->record);
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        if ($this->frm->isSubmitted()) {

            // get the status
            $status = \SpoonFilter::getPostValue('status', array('active', 'draft'), 'active');

            $this->frm->cleanupFields();

            // validation
            $fields = $this->frm->getFields();

            $this->frm->getField('publish_on_date')->isValid(Language::err('DateIsInvalid'));
            $this->frm->getField('publish_on_time')->isValid(Language::err('TimeIsInvalid'));


            foreach ($this->languages as $key => $language) {
                $field = $this->frm->getField('name_'. $this->languages[$key]['abbreviation'])->isFilled(Language::getError('FieldIsRequired'));
                $this->languages [$key]['name_errors'] = $this->frm->getField('name_'. $this->languages[$key]['abbreviation'])->getErrors();
            }


            if ($this->frm->isCorrect()) {
                $item['id'] = $this->id;
                $item['hidden'] = $fields['hidden']->getValue();
                $item['publish_on'] = Model::getUTCDate(null, Model::getUTCTimestamp($this->frm->getField('publish_on_date'), $this->frm->getField('publish_on_time')));
                $item['status'] = $status;

                $item['lightbox'] = $fields['lightbox']->isChecked() ? 'Y' : 'N';
                $item['slideshow'] = $fields['slideshow']->isChecked() ? 'Y' : 'N';

                $content = array();

                foreach ($this->languages as $language) {
                    $specific['album_id'] = $item['id'];
                    $specific['language'] = $language['abbreviation'];
                    $specific['name'] = $this->frm->getField('name_'. $language['abbreviation'])->getValue();
                    $specific['description'] = $this->frm->getField('description_'. $language['abbreviation'])->getValue() ? $this->frm->getField('description_'. $language['abbreviation'])->getValue() : null;
                    $specific['slideshow_extra_id'] = $this->record['content'][$language['abbreviation']]['slideshow_extra_id'];
                    $specific['lightbox_extra_id'] = $this->record['content'][$language['abbreviation']]['lightbox_extra_id'];

                    $content[$language['abbreviation']] = $specific;
                }



                BackendPhotogalleryModel::update($item);
                BackendPhotogalleryModel::updateContent($content, $item['id'], $item);

                Model::triggerEvent(
                    $this->getModule(), 'after_edit', $item
                );
                $this->redirect(
                    Model::createURLForAction('Edit') . '&report=edited&id=' . $item['id']
                );
            }
        }
    }
}
