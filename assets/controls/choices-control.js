choicesItemView = elementor.modules.controls.BaseData.extend({
    choices: null,
    onReady: function() {
        var options = _.extend({
            removeItemButton: true,
        }, this.model.get('choices_options'));

        var choices = new Choices(this.ui.select[0], options);

        this.ui.select[0].choices = choices;
    },
});

elementor.addControlView('choices', choicesItemView);
