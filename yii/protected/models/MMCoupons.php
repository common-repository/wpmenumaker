<?php
class MMCoupons extends CActiveRecord {

    public $id;
    public $name;
    public $code;
    public $amount;
    public $type = 0;
    public $cfrom;
    public $cto;
    public $active = 0;

    static public function model($classname = __CLASS__) {
        return parent::model($classname);
    }

    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'name' => 'Coupon name',
            'code' => 'Promo code',
            'amount' => 'Discount ammount',
            'type' => 'Discount type',
            'cfrom' => 'Active from',
            'cto' => 'Active to',
            'active' => 'Enable coupon',
        );
    }

    protected function beforeValidate() {
        if(parent::beforeValidate()){
            $pos=strpos($this->cfrom, '-');
            $tempcfrom=$this->cfrom;
            $this->cto=date('Y-m-d H:i:s',strtotime(substr($tempcfrom,$pos+1)));
            $this->cfrom=date('Y-m-d H:i:s',strtotime(substr($tempcfrom,0,$pos-1)));
            ///exit(var_dump($this->cfrom));
            return true;
        }
        return true;
    }

    public function getInRange() {
        if(Helpers::check_in_range($this->cfrom, $this->cto, date('Y-m-d H:i:s',  time())))
            return 'Active';
        else
            return 'Inactive';
    }

    public function getUrl() {
        return 'admin.php?page=wpmm_orders&action=editcoupon&id=' . $this->id;
    }

    public function getDelUrl() {
        return 'admin.php?page=wpmm_orders&action=deletecoupon&id=' . $this->id;
    }

    public function rules() {
        return array(
            array('name, code, amount, type, cfrom, cto, active', 'required'),
            array('code', 'unique'),
            array('amount', 'numerical'),
            array('name', 'length', 'max' => 250),
            array('code', 'length', 'max' => 150),
            array('type', 'in', 'range' => array(0, 1)),
            array('active', 'in', 'range' => array(0, 1)),
            array('name, id, code, amount, type, cfrom, cto, active', 'safe', 'on' => 'search'),
        );
    }

    public function search() {
        $criteria = new CDbCriteria;
        $criteria->compare('id', $this->id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('code', $this->code, true);
        $criteria->compare('cfrom', $this->cfrom, true);
        $criteria->compare('cto', $this->cto, true);
        $criteria->compare('active', $this->active, true);
        $criteria->compare('type', $this->type, true);
        return new CActiveDataProvider($this, array(
            'pagination' => array('pageSize' => '50'),
            'criteria' => $criteria,
            'sort' => array(
            'defaultOrder' => 'id DESC',
         ),
        ));
    }

    public function tableName() {
        return Wpmm::getTableNames('coupons');
    }

}