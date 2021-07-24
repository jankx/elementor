choicesItemView = elementor.modules.controls.BaseData.extend({
    choices: null,
    onReady: function() {
        // See more Choices option at here: https://github.com/Choices-js/Choices
        var options = _.extend({
            removeItemButton: true,
            shouldSort: false,
            shouldSortItems: false,
        }, this.model.get('choices_options'));


        var choices = new Choices(this.ui.select[0], options);

        this.ui.select[0].choices = choices;
    },
});

elementor.addControlView('choices', choicesItemView);
