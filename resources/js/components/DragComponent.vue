<template>
    <div ref="draggableContainer" id="draggable-container" @mousedown="dragMouseDown">
        <slot name="dragelement"></slot>
    </div>
</template>

<style scoped>
    #draggable-container {
        position: absolute;
        z-index: 9;
    }
</style>

<script>

export default {
    data: function () {
        return {
            positions: {
                clientX: undefined,
                clientY: undefined,
                movementX: 0,
                movementY: 0
            }
        }
    },
    methods: {
        dragMouseDown: function (event) {
            event.preventDefault()
            // get the mouse cursor position at startup:
            this.positions.clientX = event.clientX;
            this.positions.clientY = event.clientY;
            document.onmousemove = this.elementDrag;
            document.onmouseup = this.closeDragElement;
        },
        elementDrag: function (event) {
            event.preventDefault()
            this.positions.movementX = this.positions.clientX - event.clientX;
            this.positions.movementY = this.positions.clientY - event.clientY;
            this.positions.clientX = event.clientX;
            this.positions.clientY = event.clientY;
console.log("ON DRAG");
            // set the element's new position:
            this.$refs.draggableContainer.style.top = (this.$refs.draggableContainer.offsetTop - this.positions.movementY) + 'px'
            this.$refs.draggableContainer.style.left = (this.$refs.draggableContainer.offsetLeft - this.positions.movementX) + 'px'
        },
        closeDragElement () {
            document.onmouseup = null
            document.onmousemove = null
        }
    },
    mounted() {
        console.log('Dragable Component mounted.');

    }
}



</script>