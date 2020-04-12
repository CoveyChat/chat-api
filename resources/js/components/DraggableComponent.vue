<template>
    <span>
    </span>
</template>
<style scoped>

</style>


<script>

export default {
    props: {
        derpderp: { type: Function }
    },
    data: function () {
        return {

        }
    },
    methods: {
        onClick(e) {
            var vm = this;
            navigator.clipboard.writeText(vm.text);
            vm.showCopiedBadge = true;

            setTimeout(function() {
                vm.showCopiedBadge = false
            }, 300);
        }
    },
    update() {
        console.log('Draggable Component mounted.');
        var vm = this;

        el.style.position = 'absolute';
        var self, startX, startY, initialMouseX, initialMouseY, deadzoned;
        self = this;
        function onMove(x, y) {
            var dx = x - initialMouseX;
            var dy = y - initialMouseY;

            //30x30 deadzone to
            if(Math.abs(dx) < 30 && Math.abs(dy) < 30) {
                console.log("Deadzoned?");
                return false;
            }

            deadzoned = false;
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
            e.preventDefault();
        }

        function movedone(e) {
            if(deadzoned) {
                console.log("CALL IT");
                console.log(vnode);
                console.log(this);
                console.log(self);
                console.log(binding);
                //vnode.context[binding.expression]("onclick");
                //vnode.context.$vnode.context.$emit("derpderp");
                //self.$emit('derpderp')
            }
            //Dirty way to keep the attribute incase there's any trickle down events
            setTimeout(function() {
                el.removeAttribute("is-dragging");
            }, 100);
          }

          function mouseup(e) {
            document.removeEventListener('mousemove', mousemove);
            document.removeEventListener('mouseup', mouseup);

            movedone(e);
        }

        function touchend(e) {
            document.removeEventListener('touchmove', mousemove);
            document.removeEventListener('touchend', mouseup);

            movedone(e);
        }

        el.addEventListener('mousedown', function(e) {
            deadzoned = true;
            startX = el.offsetLeft;
            startY = el.offsetTop;
            initialMouseX = e.clientX;
            initialMouseY = e.clientY;
            document.addEventListener('mousemove', mousemove);
            document.addEventListener('mouseup', mouseup);

            return false;
        });

        el.addEventListener('touchstart', function(e) {
            deadzoned = true;
            startX = el.offsetLeft;
            startY = el.offsetTop;
            initialMouseX = e.touches[0].clientX;
            initialMouseY = e.touches[0].clientY
            e.preventDefault();
            document.addEventListener('touchmove', touchmove);
            document.addEventListener('touchend', touchend);

            return false;
        });






    }
}

</script>
