var Taco = Taco || {};
Taco.Util = Taco.Util || {};
Taco.Util.General = Taco.Util.General || {};

// taken from phpjs.org
Taco.Util.General.empty = function(mixed_var) {
  var undef, key, i, len;
  var emptyValues = [undef, null, false, 0, '', '0'];

  for (i = 0, len = emptyValues.length; i < len; i++) {
    if (mixed_var === emptyValues[i]) {
      return true;
    }
  }
  if (typeof mixed_var === 'object') {
    for (key in mixed_var) {
      // TODO: should we check for own properties only?
      //if (mixed_var.hasOwnProperty(key)) {
      return false;
      //}
    }
    return true;
  }
  return false;
};

Taco.Util.General.getParamNames = function(func) {
  var strip_comments = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
  var argument_names = /([^\s,]+)/g;
  var fnStr = func.toString().replace(strip_comments, '');
  var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(argument_names);
  if(result === null)
     result = [];
  return result;
};
