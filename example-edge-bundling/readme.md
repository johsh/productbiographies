An implementation of [Danny Holten](http://www.win.tue.nl/~dholten/)'s *hierarchical edge bundling* algorithm in [D3](http://d3js.org/), showing dependencies between classes in a software class hierarchy. Dependencies are bundled according to the parent packages. This example uses two layouts: a radial d3.layout.cluster to position the tree nodes, and d3.layout.bundle to group the dependencies into spline bundles. Thanks to [Jason Davies](http://www.jasondavies.com/) for contributing the layout implementation!

Compare to this [treemap layout](/mbostock/4341134).

See also the [interactive version](/mbostock/7607999) with link highlighting!