var Taco = Taco || {};
Taco.Util = Taco.Util || {};
Taco.Util.HTML = Taco.Util.HTML || {};

Taco.Util.HTML.render = function(str, html) {
  html = (typeof html != 'undefined')
    ? html
    : false;
  return (html === true)
    ? str
    : Taco.Util.Str.htmlEntities(str);
};

Taco.Util.HTML.attribs = function(attribs, leading_space) {

  leading_space = (typeof leading_space != 'undefined')
    ? leading_space
    : true;
  if(Taco.Util.Obj.getObjectLength(attribs) < 1) return '';

  var out = [];
  for(var key in attribs) {

    var value = attribs[key];
    value = (typeof value == 'object') ? this.objectJoin(' ', value) : value;
    out.push(key + '="' + String(value).replace(/\"/, '\"') + '"');
  }
  return ((leading_space) ? ' ' : '') + out.join(' ');
};

Taco.Util.HTML.getTextInputTypes = function() {
  return [
    'text',
    'image',
    'file',
    'search',
    'email',
    'url',
    'tel',
    'number',
    'range',
    'date',
    'month',
    'week',
    'time',
    'datetime',
    'datetime-local',
    'color'
  ];
};

Taco.Util.HTML.tag = function(element_type, body, attribs, close, is_html) {
  body = (typeof body == 'undefined' || body === null)
    ? ''
    : body;
  attribs = (typeof attribs == 'undefined')
    ? []
    : attribs;
  close = (typeof close == 'undefined')
    ? true
    : close;
  is_html = (typeof is_html == 'undefined')
    ? false
    : is_html;

  var not_self_closing = ['a', 'div', 'iframe', 'textarea'];
  var is_self_closing = false;
  if(close && Taco.Util.General.empty(body) && !Taco.Util.Arr.inArray(
    element_type.toLowerCase(),
    not_self_closing
  )) {
    is_self_closing = true;
  }

  if(is_self_closing) {
    return '<' + element_type + this.attribs(attribs) + ' />';
  }
  return [
    '<' + element_type + this.attribs(attribs) + '>',
    this.render(body, is_html),
    (close) ? '</' + element_type + '>' : ''
  ].join('');
};

Taco.Util.HTML.selecty = function(options, selected, attribs) {
  selected = (typeof selected != 'undefined')
    ? selected
    : null;
  attribs = (typeof attribs != 'undefined')
    ? attribs
    : [];
  var htmls = [];
  htmls.push(this.tag('select', null, attribs, false));

  if(Taco.Util.Obj.isIterable(options)) {
    for(var key in options) {
      value = options[key];
      var option_attribs = { value: key };
      if(String(selected) === String(value)) {
        option_attribs.selected = 'selected';
      }
      htmls.push(this.tag('option', value, option_attribs));
    }
  }
  htmls.push('</select>');
  return htmls.join("\n");
};
