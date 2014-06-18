var width = 960,
    height = 500;

var fill = d3.scale.ordinal()
    .range(colorbrewer.Greys[9].slice(1, 4));

var stroke = d3.scale.linear()
    .domain([0, 1e4])
    .range(["brown", "steelblue"]);

var treemap = d3.layout.treemap()
    .size([width, height])
    .value(function(d) { return d.size; });

var bundle = d3.layout.bundle();

var div = d3.select("#chart").append("div")
    .style("position", "relative")
    .style("width", width + "px")
    .style("height", height + "px");

var line = d3.svg.line()
    .interpolate("bundle")
    .tension(.85)
    .x(function(d) { return d.x + d.dx / 2; })
    .y(function(d) { return d.y + d.dy / 2; });

d3.json("flare-imports.json", function(classes) {
  console.log("classes");
  console.log(classes);

  //var root = packages.root(classes);
  nodes = treemap.nodes(packages.root(classes));
  nodes.forEach(function(n){
    //n.x = Math.random()*width;
    //n.y = Math.random()*height;
    n.dx = 0;
    n.dy = 0;
  })
  links = packages.imports(nodes);

  console.log("nodes");
  console.log(nodes);

  console.log("links");
  console.log(links);
  console.log(bundle(links));

  div.append("svg")
      .attr("width", width)
      .attr("height", height)
      .style("position", "absolute")
    .selectAll("path.link")
      .data(bundle(links))
    .enter().append("path")
      .style("stroke", function(d) { 
        console.log(d);
        return stroke(d[0].value); })
      .attr("class", "link")
      .attr("d", line);
});