<?php

namespace Frontend\Modules\Photogallery\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Photogallery\Engine\Model as FrontendPhotogalleryModel;

class Lightbox extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        $this->header->addJS('/src/Frontend/Modules/Photogallery/Js/jquery.magnific-popup.js');

        if (isset($this->data['id'])) {
            $this->tpl->assign('widgetPhotogalleryLightbox', FrontendPhotogalleryModel::get($this->data['id']));
        }
    }
}
