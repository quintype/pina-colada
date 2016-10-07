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

Twig.extend(function(Twig) {
  var importFile = Twig.Template.prototype.importFile;
  Twig.Template.prototype.importFile = function(path) {
    var cachedTemplate = Twig.Templates.registry[path] || Twig.Templates.registry["/" + path];
    if(cachedTemplate)
      return cachedTemplate;
    if(console) console.warn("Unable to find template: ", path);
    importFile.call(this, path);
  }
});

require("../../../resources/views/story/story.twig");

var TEMPLATES = {
  "home_body": require("../../../resources/views/home/body.twig")
};

module.exports = TEMPLATES;
