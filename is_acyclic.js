function is_acyclic(cy2){
	// if graph empty, it is acyclic
	n = cy2.nodes().length
	if(n == 0){
		return true
	} else {
		l = cy2.nodes().leaves()

		//if there are no leaf nodes it is cyclic
		if(l.length == 0){
			return false
		} else {
			cy2.remove(l)
			return is_acyclic(cy2)
		}
	}
}

function format_model_test(name, pass, fail_message){
	var p = pass == true ? "PASS" : "FAIL"
	var msg = pass == true ? "" : fail_message
	var s = "Test: " + name  + ": " + p + ". " + msg
	return s
}

function ts_check_roots(){
	var root_nodes = cy.nodes().roots()
	var msg = ""
	var pass = true
	if(root_nodes.length == 0){
		msg = "A decision tree must have a root node, none detected"
		pass = false
	}
	if(root_nodes.length > 1){
		msg = "A decision tree must have exactly one root note, multiple detected"
		pass = false
	}
	var r = {pass: pass, msg: msg}
	return r
}

function ts_check_comps(){
	var comps = cy.elements().components()
	var msg = ""
	var pass = true
	if(comps.length == 0){
		msg = "No components detected, is the graph empty?"
		pass = false
	}
	if(comps.length > 1){
		msg = comps.length.toString() + " separate components detected, they must be connected."
		pass = false
	}
	var r = {pass: pass, msg: msg}
	return r
}

function run_all_dt_tests(){
	//test model and metadata for errors fixable by the user
	//i.e. not things created by the tool

	var res = []

	//must be acyclic
	//!!!!! current test will era
	var cy2 = cytoscape({
		elements: cy.json().elements
	});
	var acyc = is_acyclic(cy2)
	res.push(format_model_test("acyclic", acyc, 'There are cycles in the tree (child node connected to a parent)'))

	//must have exactly one root node
	var root = ts_check_roots()
	res.push(format_model_test("root node", root.pass, root.msg))
	res.push()

	//must have only one component
	var comp = ts_check_comps()
	res.push(format_model_test("connected components", comp.pass, comp.msg))
	res.push()

	//all variables used in rules and rules
	var rule_vars = ts_check_rule_vars()
	res.push(format_model_test("rules", rule_vars.pass, ""))
	res = res.concat(rule_vars.msg)
	return res
}


//find all variables from a rule string
function ts_find_vars(rule_str){
	var tree = compileExpression.parser.parse(rule_str)
	var js = [];
  js.push('return ');
  function toJs(node) {
      if (Array.isArray(node)) {
          node.forEach(toJs);
      } else {
          js.push(node);
      }
  }
  tree.forEach(toJs);
  js.push(';');

	var vars = js.filter(function(el){return el.indexOf('"') >= 0})
	var varnames = []
	vars.forEach(function(el){
		varnames.push(el.replace(/"/g,''))
	})
	return varnames
}

function ts_all_rule_vars(){
	var rules = Object.keys(window.stack.metadata.dt_rules)
	//var inputs = [], outputs = [];
	var rule_map = {}
	rules.forEach(function(el){
		var r = window.stack.metadata.dt_rules[el]
		rule_map[r.name] = {inputs: [], outputs: []}
		var s = r.if_str
		var ins = ts_find_vars(s)
		//inputs = inputs.concat(ins)
		rule_map[r.name].inputs = ins
		if(r.then_str == 'set-val'){
				//outputs.push(r.then.set_variable)
				rule_map[r.name].outputs.push(r.then.set_variable)
		}
	})
	//var res = {inputs: _.uniq(inputs), outputs: _.uniq(outputs)}
	return rule_map;
}


function ts_check_rule_vars(){
	var rules = ts_all_rule_vars()
	var log = []
	var pass = true
	var all_vars = _.keys(window.stack.metadata.variables)
	var in_miss, out_miss;
	var rule_names = _.keys(rules)
	rule_names.forEach(function(el){
		in_miss = _.difference(rules[el].inputs, all_vars)
		out_miss = _.difference(rules[el].outputs, all_vars)
		if(in_miss.length != 0){
			log.push("--> rule '" + el + "' missing input: " + in_miss.join(", "))
		}
		if(out_miss.length != 0){
			log.push("--> rule '" + el + "' missing output: " + out_miss.join(", "))
		}
	})
	if(log.length > 0){
		pass = false
	}
	return {pass: pass, msg: log}
}



function ui_run_all_tests(){
	var log = run_all_dt_tests()

	log_el = document.getElementById('dt_test_log')
	log_el.innerHTML = ""
	log.forEach(function(el){
		log_el.innerHTML += el + "<br/>"
	})
}

function tests_refresh(){
	log_el = document.getElementById('dt_test_log')
	log_el.innerHTML = ""
}
