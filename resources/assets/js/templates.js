var _ = require("lodash");
var Twig = require("twig");

var FocusedImage = require("quintype-js").FocusedImage;

global.transformTemplates = function (x) {
    return _.extend(x, {
        id: x.id.replace(/resources\/views\//, "").replace(/.twig/, ''),
        path: x.path.replace(/resources\/views\//, "").replace(/.twig/, '')
    })
};


var TEMPLATES = {
    "home_body": require("../../../resources/views/home/body.twig"),
    "home_story": require("../../../resources/views/home/story.twig")
};

Twig.extendFunction("focusedImageUrl", function (slug, aspectRatio, metadata, options) {
    var cdn = global.qtConfig["image-cdn"];
    var image = new FocusedImage(slug, metadata);
    return cdn + "/" + image.path(aspectRatio, options);
});

Twig.extend(function (Twig) {
    Twig.Template.prototype.importFile = function (file) {
        var url, sub_template;
        if (!this.url && this.options.allowInlineIncludes) {
            sub_template = Twig.Templates.load("/" + file);

            if (!sub_template) {
                sub_template = Twig.Templates.loadRemote(url, {
                    id: file,
                    method: this.getLoaderMethod(),
                    async: false,
                    options: this.options
                });

                if (!sub_template) {
                    throw new Twig.Error("Unable to find the template " + file);
                }
            }

            sub_template.options = this.options;

            return sub_template;
        }

        url = Twig.path.parsePath(this, file);

        // Load blocks from an external file
        sub_template = Twig.Templates.loadRemote(url, {
            method: this.getLoaderMethod(),
            base: this.base,
            async: false,
            options: this.options,
            id: url
        });

        return sub_template;
    }
});

module.exports = window.ooga = TEMPLATES;
