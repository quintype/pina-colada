global.$ = global.jQuery = require("jquery");
require("jquery.marquee");

global.app = {
  initQtReady: require("./qt_ready"),
  startHomePreview: require("./home_preview"),
  startStoryPreview: require("./story_preview"),
  video: require("./video"),
  quintypeLoadMore : require("./load_more"),
  mapOverlay : require("./map_overlay"),
  slickSlideShow : require("./slick_slideShow_settings"),
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
