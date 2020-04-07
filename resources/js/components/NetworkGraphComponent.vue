<template>
    <div id="active-network-chart">

    </div>
</template>

<style>
    .link {
        stroke: #aaa;
    }

    .node text {
        stroke:#333;
        cursor:pointer;
    }

    .node circle{
        stroke:#fff;
        stroke-width:3px;
        fill:#555;
    }
</style>

<script>
    export default {
        mounted() {
            console.log("Network Graph Mounted");
        },
        data: function () {
            return {
                height: 0,
                width: 0,
                simulation: null,
                link: null,
                node: null,
                label: null,
                color: null,
                connections: {
                    "nodes": [
                    {
                    "id": "me",
                    "name": "Me"
                    }
                ],
                "links": []
                },
            }
        },
        methods: {
            init () {
                var vm = this;
                vm.color = d3.scaleOrdinal(d3.schemeTableau10);
                console.log("Network Graph Mounted!");

                // set the dimensions and margins of the graph
                var margin = {top: 10, right: 30, bottom: 30, left: 40};
                vm.width = 600;
                vm.height = 300;


                let svg = d3.select("#active-network-chart")
                    .append("svg")
                    .attr("width", vm.width)
                    .attr("height", vm.height)
                    .attr("viewBox", [-vm.width / 2, -vm.height / 2, vm.width, vm.height]);

                vm.simulation = d3.forceSimulation()
                    .force("charge", d3.forceManyBody().strength(-400))
                    .force("link", d3.forceLink().id(d => d.id).distance(75))
                    .force("x", d3.forceX())
                    .force("y", d3.forceY())
                    .on("tick", vm.tick);

                vm.link = svg.append("g")
                    .attr("stroke", "#000")
                    .attr("stroke-width", 1.5)
                    .selectAll("line");

                vm.node = svg.append("g")
                    .attr("stroke", "#fff")
                    .attr("stroke-width", 1.5)
                    .selectAll("circle");

                vm.label = svg.append("g")
                    .attr("class", "labels")
                    .selectAll("text")


                vm.update(vm.connections);

            },
            update(data) {
                var vm = this;

                // Make a shallow copy to protect against mutation, while
                // recycling old nodes to preserve position and velocity.
                const old = new Map(vm.node.data().map(d => [d.id, d]));

                data.nodes = data.nodes.map(d => Object.assign(old.get(d.id) || {}, d));
                data.links = data.links.map(d => Object.assign({}, d));

                vm.node = vm.node
                    .data(data.nodes, d => d.id)
                    .join(
                        enter => enter.append("circle")
                        .attr("r", 20)
                        .attr("fill", d => vm.color(d.id))
                        .on("mouseover", vm.onHover)
                    );

                vm.link = vm.link
                    .data(data.links, d => [d.source, d.target])
                    .join("line");


                vm.label = vm.label
                    .data(data.nodes, d => d.name)
                    .join(enter => enter.append("text")
                        .attr("class", "label")
                        .text(function(d) { return d.name; })
                    );

                vm.simulation.nodes(data.nodes);
                vm.simulation.force("link").links(data.links);
                vm.simulation.alpha(1).restart();

            },
            onHover(node, index) {
                //console.log(node);
                //console.log(index);
            },
            tick() {
                var vm = this;

                vm.node.attr("cx", d => d.x)
                    .attr("cy", d => d.y)

                vm.link.attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                vm.label
                    .attr("x", function(d) { return d.x-10; })
                    .attr("y", function (d) { return d.y+5; })
                    .style("font-size", "15px")
                    .style("font-weight", "bold")
                    .style("fill", "#fff");
            }
        }
    }
</script>
