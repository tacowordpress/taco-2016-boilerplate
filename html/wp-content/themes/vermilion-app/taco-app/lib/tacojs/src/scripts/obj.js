var Taco = Taco || {};
Taco.Util = Taco.Util || {};
Taco.Util.Obj = Taco.Util.Obj || {};

Taco.Util.Obj.isIterable = function(obj) {
  if(Taco.Util.Obj.getObjectLength(obj) > -1) return true;
  return false;
};

Taco.Util.Obj.objectJoin = function(joiner, object) {
  var s = '';
  for(var o in object) {
    s += (o + ' ' + object[o]);
  }
  return s;
};

Taco.Util.Obj.getObjectLength = function(object) {
  if(typeof object != 'object') return -1;
  var count = 0;
  for(var o in object) {
    count++;
  }
  return count;
};
