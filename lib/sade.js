const EventEmitter = require('events');

/**
 * Determine if the input string is a JSON string or not.
 *
 * @param  {string} str
 *
 * @return {boolean}
 */
const isJSON = function(str) {
  if (typeof str !== 'string') {
    return false;
  }

  try {
      JSON.parse(str);
  } catch (e) {
      return false;
  }

  return true;
}

class Sade extends EventEmitter {
  /**
   * Constructor.
   */
  constructor() {
    super();
    this._listen();
  }

  /**
   * Listen to stdin.
   */
  _listen() {
    const self = this;
    var data = '';

    process.stdin.on('data', function(chunk) {
      data += chunk;
    });

    process.stdin.on('end', function() {
      if (isJSON(data)) {
        data = JSON.parse(data);
      }

      self.emit('data', data);
    });
  }

  /**
   * Write data back to Sade.
   *
   * @param {string|Buffer} data
   */
  write(data) {
      process.stdout.write(data);
  }

  /**
   * Write error back to Sade.
   *
   * @param {string|Buffer} data
   */
  writeError(data) {
    process.stderr.write(data);
  }
}

module.exports = new Sade;
