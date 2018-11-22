var data = '';
var babel = require('babel-core');
var sass = require('node-sass');

process.stdin.on('data', function(chunk) {
    data += chunk;
});

process.stdin.on('end', function() {
    data = JSON.parse(data);

    switch (data.type) {
        case 'style':
            var result = sass.renderSync({
                data: data.code,
            });
            data.code = result.css;
            break;
        case 'script':
            var result = babel.transform(data.code, {
                "presets": ["env"]
            });
            data.code = result.code;
            break;
        default:
            break;
    }

    process.stdout.write(data.code);
});