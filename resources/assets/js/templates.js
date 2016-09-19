var _ = require("lodash");
var Twig = require("twig");

var FocusedImage = require("quintype-js").FocusedImage;

global.transformTemplates = function(x) {
  return _.extend(x, {
    id: x.id.replace(/resources\/views\//, "").replace(/.twig/, ''),
    path: x.path.replace(/resources\/views\//, "").replace(/.twig/, '')
  })
};

Twig.extendFunction("focusedImageUrl", function(slug, aspectRatio, metadata, options) {
  var cdn = global.qtConfig["image-cdn"];
  var image = new FocusedImage(slug, metadata);
  return cdn + "/" + image.path(aspectRatio, options);
});

var ALL_TEMPLATES = {};
function wrapTemplate(template) {
  ALL_TEMPLATES[template.id] = template;
  var importFile = template.importFile;
  template.importFile = function(path) {
    return ALL_TEMPLATES[path] || importFile.call(template, path);
  }
  return template;
}

wrapTemplate(require("../../../resources/views/home/story.twig"));

var TEMPLATES = {
  "home_body": wrapTemplate(require("../../../resources/views/home/body.twig"))
};

module.exports = TEMPLATES;
