<?php
namespace AdvancedElementor\Controls;

use Elementor\Base_Data_Control;

class Choices extends Base_Data_Control
{
    const CONTROL_NAME = 'choices';

    public function get_type()
    {
        return static::CONTROL_NAME;
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
        $assetDir = dirname(dirname(__DIR__)) . '/assets';
        $abspath = constant('ABSPATH');
        if (PHP_OS === 'WINNT') {
            $abspath = str_replace('\\', '/', $abspath);
            $assetDir = str_replace('\\', '/', $assetDir);
        }
        $assetDirUrl = str_replace($abspath, site_url('/'), $assetDir);

        wp_register_style('choices', sprintf('%s/libs/Choices/styles/choices.min.js', $assetDirUrl), [], '9.0.1');
        wp_enqueue_style('choices');

        wp_register_script('choices', sprintf('%s/libs/Choices/scripts/choices.min.js', $assetDirUrl), [], '9.0.1');
        wp_register_script('choices-control', sprintf('%s/controls/choices-control.js', $assetDirUrl), ['choices'], '1.0.0');
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
                <select id="<?php echo $control_uid; ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
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
