var Taco = Taco || {};
Taco.Util = Taco.Util || {};
Taco.Util.Arr = Taco.Util.Arr || {};

Taco.Util.Arr.inArray = function(value, array) {
  if(Taco.Util.Obj.getObjectLength(array) < 1) return false;
  for(var a in array) {
    if(array[a] == value) return true;
  }
  return false;
};