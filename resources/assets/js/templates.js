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

Twig.extendFunction("base64_decode", function(string) {
   return atob(string);
});

require("../../../resources/views/author/body.twig");
require("../../../resources/views/author/index.twig");

require("../../../resources/views/home/body.twig");
require("../../../resources/views/home/index.twig");

require("../../../resources/views/search/body.twig");
require("../../../resources/views/search/index.twig");

require("../../../resources/views/section/body.twig");
require("../../../resources/views/section/index.twig");

require("../../../resources/views/shared/article_list.twig");
require("../../../resources/views/shared/load_more_stories.twig");

require("../../../resources/views/story/article_header.twig");
require("../../../resources/views/story/body.twig");
require("../../../resources/views/story/index.twig");
require("../../../resources/views/story/related_stories.twig");
require("../../../resources/views/story/share.twig");
require("../../../resources/views/story/story_card.twig");
require("../../../resources/views/story/story_elements.twig");
require("../../../resources/views/story/tags.twig");

require("../../../resources/views/story/social_icons/facebook_svg_icon.twig");
require("../../../resources/views/story/social_icons/gplus_svg_icon.twig");
require("../../../resources/views/story/social_icons/img_expanded.twig");
require("../../../resources/views/story/social_icons/linkedin_svg_icon.twig");
require("../../../resources/views/story/social_icons/slide_close.twig");
require("../../../resources/views/story/social_icons/twitter_svg_icon.twig");
require("../../../resources/views/story/social_icons/whatsapp_svg_icon.twig");

require("../../../resources/views/story/story_elements/composite.twig");
require("../../../resources/views/story/story_elements/image.twig");
require("../../../resources/views/story/story_elements/jsembed.twig");
require("../../../resources/views/story/story_elements/references.twig");
require("../../../resources/views/story/story_elements/soundcloud-audio.twig");
require("../../../resources/views/story/story_elements/text.twig");
require("../../../resources/views/story/story_elements/title.twig");
require("../../../resources/views/story/story_elements/youtube-video.twig");

require("../../../resources/views/story/story_elements/story_elements_sub_type/bigfact.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/blockquote.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/blurb.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/image-gallery.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/q-and-a.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/quote.twig");
require("../../../resources/views/story/story_elements/story_elements_sub_type/summary.twig");

require("../../../resources/views/tag/body.twig");
require("../../../resources/views/tag/index.twig");


var TEMPLATES = {
  "home_body": require("../../../resources/views/home/body.twig"),
  "story_body": require("../../../resources/views/story/body.twig"),
  "list_articles": require("../../../resources/views/shared/article_list.twig")
};

module.exports = TEMPLATES;
