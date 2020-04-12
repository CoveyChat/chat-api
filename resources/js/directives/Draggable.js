
Vue.directive('draggable', {
    update: function (el) {
        el.style.position = 'absolute';
        var startX, startY, initialMouseX, initialMouseY;

        function onMove(x, y) {
            var dx = x - initialMouseX;
            var dy = y - initialMouseY;

            //30x30 deadzone to
            if(Math.abs(dx) < 30 && Math.abs(dy) < 30) {
                console.log("Deadzoned?");
                return false;
            }
            //console.log("TOP: " + (startY + dy) + "LEFT: " + (startX + dx));

            el.style.cssText = 'top: ' + (startY + dy) + 'px !important;left: ' + (startX + dx) + 'px !important; right: unset !important; bottom: unset !important';
            el.setAttribute("is-dragging", true);
            return false;
        }

        function mousemove(e) {
            onMove(e.clientX, e.clientY);
        }

        function touchmove(e) {
            onMove(e.touches[0].clientX, e.touches[0].clientY);
        }

        function movedone() {
            //Dirty way to keep the attribute incase there's any trickle down events
            setTimeout(function() {
                el.removeAttribute("is-dragging");
            }, 100);
          }

          function mouseup() {
            document.removeEventListener('mousemove', mousemove);
            document.removeEventListener('mouseup', mouseup);

            movedone();
        }

        function touchend() {
            document.removeEventListener('touchmove', mousemove);
            document.removeEventListener('touchend', mouseup);

            movedone();
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

        el.addEventListener('touchstart', function(e) {
            startX = el.offsetLeft;
            startY = el.offsetTop;
            initialMouseX = e.touches[0].clientX;
            initialMouseY = e.touches[0].clientY

            document.addEventListener('touchmove', touchmove);
            document.addEventListener('touchend', touchend);
            return false;
        });
    }
  });
