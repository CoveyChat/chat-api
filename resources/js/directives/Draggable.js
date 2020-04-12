
Vue.directive('draggable', {
    update: function (el) {
      el.style.position = 'absolute';
      var startX, startY, initialMouseX, initialMouseY;

      function mousemove(e) {
        var dx = e.clientX - initialMouseX;
        var dy = e.clientY - initialMouseY;


        //30x30 deadzone to
        if(Math.abs(dx) < 30 && Math.abs(dy) < 30) {
            return false;
        }
        el.style.cssText = 'top: ' + (startY + dy) + 'px !important;left: ' + (startX + dx) + 'px !important; right: unset !important; bottom: unset !important';
        el.setAttribute("is-dragging", true);
        return false;
      }

      function mouseup() {
        document.removeEventListener('mousemove', mousemove);
        document.removeEventListener('mouseup', mouseup);
        //Dirty way to keep the attribute incase there's any trickle down events
        setTimeout(function() {
            el.removeAttribute("is-dragging");
        }, 100);

      }

      el.addEventListener('mousedown', function(e) {
        startX = el.offsetLeft;
        startY = el.offsetTop;
        initialMouseX = e.clientX;
        initialMouseY = e.clientY;
        document.addEventListener('mousemove', mousemove);
        document.addEventListener('mouseup', mouseup);
        return false;
      });
    }
  });
