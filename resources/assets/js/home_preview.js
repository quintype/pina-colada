var template = require("./templates").home_body;

module.exports = function() {
  window.addEventListener("message", function(event){
    var story = event.data['story'];
    if (story) {
      var html = template.render({
        stories: Array(20).fill(story),
        preview: true
      });
      $("#container").html(html);
    }
  });
}
