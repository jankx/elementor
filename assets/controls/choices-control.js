var choicesItemView = elementor.modules.controls.BaseData.extends({
    saveValue: function() {
        alert('zo');
    }
});
elementor.addControlView('choices', choicesItemView);
