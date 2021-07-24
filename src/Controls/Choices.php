<?php
namespace AdvancedElementor\Controls;

class Choices extends BaseControl
{
    const CONTROL_NAME = 'choices';

    public function get_type()
    {
        return static::CONTROL_NAME;
    }

    protected function get_control_uid($input_type = 'default')
    {
        return 'elementor-control-' . $input_type . '-{{{ data._cid }}}';
    }

    protected function get_default_settings()
    {
        return [
            'options' => [],
            'multiple' => false,
            // Select2 library options
            'select2options' => [],
            // the lockedOptions array can be passed option keys. The passed option keys will be non-deletable.
            'lockedOptions' => [],
        ];
    }

    public function enqueue()
    {
        wp_register_style(
            'choices',
            $this->get_asset_url('libs/Choices/styles/choices.min.css'),
            [],
            '9.0.1'
        );
        wp_enqueue_style('choices');

        wp_register_script(
            'choices',
            $this->get_asset_url('libs/Choices/scripts/choices.min.js'),
            [],
            '9.0.1',
            true
        );
        wp_register_script(
            'choices-control',
            $this->get_asset_url('controls/choices-control.js'),
            ['choices', 'elementor-editor'],
            '1.0.0.15',
            true
        );

        wp_enqueue_script('choices-control');
    }

    public function content_template()
    {
        $control_uid = $this->get_control_uid();
        ?>
        <div class="elementor-control-field">
            <# if ( data.label ) {#>
                <label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <# } #>
            <div class="elementor-control-input-wrapper elementor-control-unit-5">
                <# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
                <select id="<?php echo $control_uid; ?>" class="advanced-elementor-choices" type="choices" {{ multiple }} data-setting="{{ data.name }}">
                    <# _.each( data.options, function( option_title, option_value ) {
                        var value = data.controlValue;
                        if ( typeof value == 'string' ) {
                            var selected = ( option_value === value ) ? 'selected' : '';
                        } else if ( null !== value ) {
                            var value = _.values( value );
                            var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : '';
                        }
                        #>
                    <option {{ selected }} value="{{ option_value }}">{{{ option_title }}}</option>
                    <# } ); #>
                </select>
            </div>
        </div>
        <# if ( data.description ) { #>
            <div class="elementor-control-field-description">{{{ data.description }}}</div>
        <# } #>
        <?php
    }
}
