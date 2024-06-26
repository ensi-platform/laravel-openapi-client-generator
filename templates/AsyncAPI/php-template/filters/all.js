const filter = module.exports;
const _ = require('lodash');

function toPHPType(prop) {
    if (isDateOrDateTime(prop)) {
        return 'DateTime';
    }

    switch (prop.type()) {
        case 'integer':
        case 'int32':
            return 'int';
        case 'long':
        case 'int64':
            return 'int';
        case 'boolean':
            return 'bool';
        case 'date':
        case 'time':
        case 'dateTime':
        case 'date-time':
            return 'DateTime';
        case 'string':
        case 'password':
        case 'byte':
            return 'string';
        case 'float':
        case 'double':
            return 'float';
        default:
            return prop.uid();
    }
}

filter.toPHPType = toPHPType;

function isDateOrDateTime(prop) {
    switch (prop.format()) {
        case 'date':
        case 'time':
        case 'dateTime':
        case 'date-time':
            return 'DateTime';
    }

    return false;
}

filter.isDateOrDateTime = isDateOrDateTime;

function isEnum(prop) {
    return prop.format() === 'enum';
}

filter.isEnum = isEnum;

function isNullableOrNotRequired(prop, propName) {
    if (typeof prop._json === 'boolean') {
        return false;
    }

    return (prop._json.nullable ?? false) || !isRequired(propName, prop._meta.parent.required() ?? []);
}

filter.isNullableOrNotRequired = isNullableOrNotRequired;

function isDefined(obj) {
    return typeof obj !== 'undefined'
}

filter.isDefined = isDefined;

function isProtocol(api, protocol) {
    return JSON.stringify(api.json()).includes('"protocol":"' + protocol + '"');
}
filter.isProtocol = isProtocol;

function getDefaultProtocol(api) {
    const {servers} = api.json();
    const firstAvailableServer = servers[Object.keys(servers)[0]];

    return firstAvailableServer.protocol;
}

filter.getDefaultProtocol = getDefaultProtocol;

function examplesToString(ex) {
    let retStr = "";
    ex.forEach(example => {
        if (retStr !== "") {
            retStr += ", "
        }
        if (typeof example == "object") {
            try {
                retStr += JSON.stringify(example);
            } catch (ignore) {
                retStr += example;
            }
        } else {
            retStr += example;
        }
    });
    return retStr;
}
filter.examplesToString = examplesToString;

function splitByLines(str) {
    if (str) {
        return str.split(/\r?\n|\r/).filter((s) => s !== "");
    } else {
        return "";
    }
}
filter.splitByLines = splitByLines;

function isRequired(name, list) {
    return list && list.includes(name);
}
filter.isRequired = isRequired;

function debug(val) {
    return JSON.stringify(val, null, "\t");
}

filter.debug = debug;

function toPascalCase(val) {
    return _.upperFirst(_.camelCase(val));
}

filter.toPascalCase = toPascalCase;

function getPropertyMethods(method, property) {
    return method + _.upperFirst(_.camelCase(property));
}

filter.getPropertyMethods = getPropertyMethods;
