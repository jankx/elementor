choicesItemView = elementor.modules.controls.BaseData.extend({
    choices: null,
    onReady: function() {
        var options = _.extend({
        }, this.model.get('choices_options'));

        console.log(this);

        choices = new Choices(this.ui.select[0]);
        this.ui.select[0].choices = choices;
    },
});

elementor.addControlView('choices', choicesItemView);
