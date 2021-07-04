<?php
/**
 * @filesource modules/inventory/views/write.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Write;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-write&tab=product
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มเพิ่ม/แก้ไข Inventory
     *
     * @param Request $request
     * @param object $product
     *
     * @return string
     */
    public function render(Request $request, $product)
    {
        $form = Html::create('form', array(
            'id' => 'product',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/inventory/model/write/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Details of} {LNG_Equipment}',
        ));
        $groups = $fieldset->add('groups');
        if ($product->id == 0) {
            // product_no
            $groups->add('text', array(
                'id' => 'product_no',
                'labelClass' => 'g-input icon-number',
                'itemClass' => 'width50',
                'label' => '{LNG_Serial/Registration No.}',
                'maxlength' => 20,
                'value' => isset($product->product_no) ? $product->product_no : '',
            ));
        }
        // topic
        $groups->add('text', array(
            'id' => 'topic',
            'labelClass' => 'g-input icon-edit',
            'itemClass' => 'width50',
            'label' => '{LNG_Equipment}',
            'placeholder' => '{LNG_Details of} {LNG_Equipment}',
            'maxlength' => 64,
            'value' => isset($product->topic) ? $product->topic : '',
        ));
        // category
        $category = \Inventory\Category\Model::init();
        $n = 0;
        foreach (Language::get('INVENTORY_CATEGORIES', array()) as $key => $label) {
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($product->{$key}) ? $product->{$key} : 0,
                'text' => '',
            ));
            $n++;
        }
        foreach (Language::get('INVENTORY_METAS', array()) as $key => $label) {
            if ($key == 'detail') {
                $fieldset->add('textarea', array(
                    'id' => $key,
                    'labelClass' => 'g-input icon-file',
                    'itemClass' => 'item',
                    'label' => $label,
                    'rows' => 3,
                    'value' => isset($product->{$key}) ? $product->{$key} : '',
                ));
            } else {
                $fieldset->add('text', array(
                    'id' => $key,
                    'labelClass' => 'g-input icon-edit',
                    'itemClass' => 'item',
                    'label' => $label,
                    'value' => isset($product->{$key}) ? $product->{$key} : '',
                ));
            }
        }
        if ($product->id == 0) {
            $groups = $fieldset->add('groups');
            // stock
            $groups->add('number', array(
                'id' => 'stock',
                'labelClass' => 'g-input icon-number',
                'itemClass' => 'width50',
                'label' => '{LNG_Stock}',
                'value' => isset($product->stock) ? $product->stock : 1,
            ));
            // unit
            $groups->add('text', array(
                'id' => 'unit',
                'labelClass' => 'g-input icon-star0',
                'itemClass' => 'width50',
                'label' => '{LNG_Unit}',
                'value' => isset($product->unit) ? $product->unit : '',
                'nameValue' => '',
            ));
        }
        // picture
        if (is_file(ROOT_PATH.DATA_FOLDER.'inventory/'.$product->id.'.jpg')) {
            $img = WEB_URL.DATA_FOLDER.'inventory/'.$product->id.'.jpg?'.time();
        } else {
            $img = WEB_URL.'modules/inventory/img/noimage.png';
        }
        $fieldset->add('file', array(
            'id' => 'picture',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Image}',
            'comment' => Language::replace('Browse image uploaded, type :type', array(':type' => 'jpg, jpeg, png')).' ({LNG_resized automatically})',
            'dataPreview' => 'imgPicture',
            'previewSrc' => $img,
            'accept' => array('jpg', 'jpeg', 'png'),
        ));
        // inuse
        $fieldset->add('select', array(
            'id' => 'inuse',
            'labelClass' => 'g-input icon-valid',
            'itemClass' => 'item',
            'label' => '{LNG_Status}',
            'options' => Language::get('INVENTORY_STATUS'),
            'value' => $product->inuse,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // id
        $fieldset->add('hidden', array(
            'id' => 'id',
            'value' => $product->id,
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
