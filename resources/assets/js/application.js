global.$ = global.jQuery = require("jquery");
require("jquery.marquee");
require("slick");

global.app = {
  initQtReady: require("./qt_ready"),
  startHomePreview: require("./home_preview"),
  startStoryPreview: require("./story_preview"),
  video: require("./video"),
  quintypeLoadMore : require("./load_more"),
  rating : require("./rating"),
  mapOverlay : require("./map_overlay"),
  slickSlideShow : require("./slick_slideshow_settings"),
  analytics: require("./analytics")
};

$(document).ready(function() {
  app.slickSlideShow.slickSettings();
  app.video.setupYoutubeVideo();
  app.video.loadYoutubeLibrary();
  app.mapOverlay.locationOverlay();
});

$('a.social-share').click(function(){
  global.app.analytics.trackStoryShare(event);
});
