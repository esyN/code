<ul class="dropdown-menu">
<li><button class="btn btn-link" onclick="cy.layout({name:'dagre',nodeDimensionsIncludeLabels: true, nodeSep:15, ranker:'tight-tree'}).run()">Decision Tree (vertical)</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'klay',nodeDimensionsIncludeLabels: true}).run()">Decision Tree (horizontal)</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'breadthfirst'}).run()">Breadthfirst</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'circle'}).run()">Circular</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'grid'}).run()">Grid</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'random'}).run()">Random</button></li>
</ul>
