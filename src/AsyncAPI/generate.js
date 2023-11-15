const Generator = require('@asyncapi/generator');
const { program } = require('commander');
const chalk = require('chalk');

program
    .requiredOption('-i, --input <char>', 'path to template dir', __dirname + '/php-template')
    .requiredOption('-o, --output <char>', 'path to output dir')
    .requiredOption('-f, --file <char>', 'path to async docs yaml file')
    .requiredOption('-n, --namespace <char>', 'Name of the package');

program.parse();

const options = program.opts();

const params = {
    templateParams: {
        "packageName": options.namespace,
    }
}

const generator = new Generator(options.input, options.output, params);

generator.generateFromFile(options.file).then(() => {
    console.log(chalk.green('The Async API has been successfully generated!'));
}).catch(function(e) {
    console.error(chalk.red(e));

    process.exit(1);
});
