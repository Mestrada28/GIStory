<?php
/**
 * Generic PHP Form Builder Class
 *
 * @version 1.0.2
 * @author Jeff Todnem
 * @link https://github.com/joshcanhelp/php-form-builder
 * @license MIT
 */
class FormBuilder {
    private $_form;
    private $_fields;

    public function __construct($form) {
        $this->_form = $form;
        $this->_fields = array();
    }

    public function addField($name, $label, $type, $options = array()) {
        $field = array(
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'options' => $options,
        );
        $this->_fields[] = $field;
    }

    public function render() {
        $html = '<form' . $this->_htmlAttributes($this->_form) . '>';
        foreach ($this->_fields as $field) {
            $html .= $this->_renderField($field);
        }
        $html .= '</form>';
        return $html;
    }

    private function _renderField($field) {
        $html = '<div class="form-group">';
        $html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
        switch ($field['type']) {
            case 'text':
                $html .= '<input type="text" id="' . $field['name'] . '" name="' . $field['name'] . '"' . $this->_htmlAttributes($field['options']) . '>';
                break;
            case 'textarea':
                $html .= '<textarea id="' . $field['name'] . '" name="' . $field['name'] . '"' . $this->_htmlAttributes($field['options']) . '></textarea>';
                break;
            case 'select':
                $html .= '<select id="' . $field['name'] . '" name="' . $field['name'] . '"' . $this->_htmlAttributes($field['options']) . '>';
                foreach ($field['options']['options'] as $option) {
                    $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
                }
                $html .= '</select>';
                break;
            case 'radio':
                foreach ($field['options']['options'] as $option) {
                    $html .= '<div class="radio">';
                    $html .= '<label><input type="radio" name="' . $field['name'] . '" value="' . $option['value'] . '"' . $this->_htmlAttributes($option) . '>' . $option['label'] . '</label>';
                    $html .= '</div>';
                }
                break;
            case 'checkbox':
                foreach ($field['options']['options'] as $option) {
                    $html .= '<div class="checkbox">';
                    $html .= '<label><input type="checkbox" name="' . $field['name'] . '[]" value="' . $option['value'] . '"' . $this->_htmlAttributes($option) . '>' . $option['label'] . '</label>';
                    $html .= '</div>';
                }
                break;
        }
        $html .= '</div>';
        return $html;
    }

    private function _htmlAttributes($attributes) {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . $value . '"';
        }
        return $html;
    }
}
?>