var Taco = Taco || {};
Taco.Util = Taco.Util || {};
Taco.Util.Str = Taco.Util.Str || {};

Taco.Util.Str.human = function(str) {
  // Cleanup
  var out;
  out = str.replace(/\-|\_/g, ' ');
  out = this.ucwords(out.toLowerCase());
  out = out.replace(/[\s]{2,}/g, ' ');
  out = out.replace(/^[\s]/g, '');
  out = out.replace(/[\s]$/, '');
  if(out.length === 0) return out;

  // Gather stopwords before looping
  var stop_words_lower = this.stopWordsLower();

  // Handle each word
  var words = out.split(' ');
  var out_words = [];
  for(var n in words) {
    var word = words[n];
    var out_word = word;

    // If we have a special match, don't do anything else
    var specials = {
      'id'   : 'ID',
      'ids'  : 'IDs',
      'url'  : 'URL',
      'urls' : 'URLs',
      'cta'  : 'CTA',
      'api'  : 'API',
      'faq'  : 'FAQ',
      'ip'   : 'IP',
      'why'  : 'why',
      'Why'  : 'Why',
    };
    var special_word = false;
    for(var regex in specials) {
      var obj_regex = new RegExp(regex, 'igm');
      var special = specials[regex];
      if(!obj_regex.test(word)) continue;
        special_word = true;
        out_word = special;
    }
    if(special_word) {
      out_words.push(out_word);
      continue;
    }

    // Handle acronyms without vowels
    if(word.search(/[aeiou]/i) == -1) {
      out_word = out_word.toUpperCase();
    }

    // Stop words
    var lower = word.toLowerCase();
    if(Taco.Util.Arr.inArray(lower, stop_words_lower) && n !== 0) {
      out_word = lower;
    }

    out_words.push(out_word);
  }
  out = out_words.join(' ');

  // Questions
  var first_word_lower = words[0].toLowerCase();
  var first_word_lower_no_contraction = first_word_lower.replace(/'s$/, '');
  var is_question = Taco.Util.Obj.isIterable(
    first_word_lower_no_contraction,
    this.questionWords()
  );
  var has_question_mark = (out.search(/[\?]{1,}$/) > -1);
  if(is_question && !has_question_mark) out += '?';

  return out;
};

Taco.Util.Str.stopWordsLower = function() {
  return ['a', 'if', 'in', 'by', 'and', 'at', 'as', 'for', 'of', 'or', 'to', 'the'];
};

Taco.Util.Str.questionWords = function() {
  return ['who', 'what', 'where', 'when', 'why', 'how', 'which', 'wherefore', 'whatever', 'whom', 'whose', 'wherewith', 'whither', 'whence'];
};

// taken from http://phpjs.org/functions/ucwords/
Taco.Util.Str.ucwords = function(str) {
  return (str + '')
    .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
      return $1.toUpperCase();
    });
};

Taco.Util.Str.htmlEntities = function(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
};