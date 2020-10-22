<ul class="dropdown-menu">
<li><button class="btn btn-link" onclick="cy.layout({name:'cola',unconstrIter:10,randomize:true,maxSimulationTime:10000}).run()">Force Directed (reset)</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'cola'}).run()">Force Directed (improve)</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'breadthfirst'}).run()">Breadthfirst Layout</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'circle'}).run()">Circular Layout</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'grid'}).run()">Grid Layout</button></li>
<li><button class="btn btn-link" onclick="cy.layout({name:'random'}).run()">Random Layout</button></li>
</ul>
