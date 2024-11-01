<?php
class FlagColumn extends CGridColumn {

    public $name;
    public $sortable = true;
    public $callbackUrl = array('flag');
    private $_flagClass = "flag_link";

    public function init() {
        parent::init();
        $cs = Yii::app()->getClientScript();
        $gridId = $this->grid->getId();
        $script = <<<SCRIPT
jQuery(".{$this->_flagClass}, .{$this->_flagClass}_no").live("click", function(e){
e.preventDefault();
var link = this;
jQuery.ajax({
type: "POST",
cache: false,
url: link.href,
success: function(data){
jQuery('#$gridId').yiiGridView.update('$gridId');
}
});
});
SCRIPT;
        $cs->registerScript(__CLASS__ . $gridId . '#flag_link', $script);
    }

    protected function renderDataCellContent($row, $data) {
        $value = CHtml::value($data, $this->name);

        $this->callbackUrl.='&pk=' . $data->primaryKey;
        $this->callbackUrl.='&name=' . urlencode($this->name);
        $this->callbackUrl.='&value=' . (int) empty($value);

        $link = CHtml::normalizeUrl($this->callbackUrl);
        if (!empty($value)) {
            $this->_flagClass = 'flag_link_no';
        }
        else
            $this->_flagClass = 'flag_link';
        echo CHtml::link('', $link, array(
            'class' => $this->_flagClass,
        ));
    }

    protected function renderHeaderCellContent() {
        if ($this->grid->enableSorting && $this->sortable && $this->name !== null){
            $label = $this->header.'<span class="caret"></span>';
            preg_match_all('/href=\"(.*?)\"/', $this->grid->dataProvider->getSort()->link($this->name, $this->header) , $matches);
            $param = substr($matches[0][0], strrpos($matches[0][0], '&'), -1);
            echo '<a class="sort-link" href="' . admin_url() . 'admin.php?page=' . $_GET['page'] . $param . '">' . $label . '</a>';
        }
        else if ($this->name !== null && $this->header === null) {
            if ($this->grid->dataProvider instanceof CActiveDataProvider)
                echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
            else
                echo CHtml::encode($this->name);
        }
        else
            parent::renderHeaderCellContent();
    }
}