<?php
/**
 * @filesource modules/borrow/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Borrow\Settings;

use Kotchasan\Html;

/**
 * module=borrow-settings.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @return string
     */
    public function render()
    {
        // form
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/borrow/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_General}',
        ));
        // borrow_no
        $fieldset->add('text', array(
            'id' => 'borrow_no',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'item',
            'label' => '{LNG_Transaction No.}',
            'comment' => '{LNG_Numeric code format, such as %YY%M%D-%04d (for example 25641231-0001)}',
            'placeholder' => 'B%Y%M%D-%04d',
            'value' => isset(self::$cfg->borrow_no) ? self::$cfg->borrow_no : 'B%Y%M%D-%04d',
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // คืนค่า HTML

        return $form->render();
    }
}
