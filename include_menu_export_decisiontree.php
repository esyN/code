<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Export<span class="caret"></span></a>
      <ul class="dropdown-menu">
      	<li><button onclick="exportDecisionTree()" class="btn btn-link" id="tc-all" >Export conditions</button></li>
        <li><button onclick="exportDecisionTreeProps()" class="btn btn-link" id="tc-all" >Export batch template</button></li>
    	<li><button onclick="exportToTC()" class="btn btn-link" id="tc-current" >Export tree as csv</button></li>
        <li><button onclick="toPNG('all')" class="btn btn-link" id="png-all" >Export tree as PNG</button></li>
		<li><button onclick="toPNG('current')" class="btn btn-link" id="png-current" >Export view as PNG</button></li>
      </ul>
    </li>
