<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Payloads;

use DateTime;

/**{%- for propName, prop in schema.properties() %}{%- set canBeNull = (prop | isNullableOrNotRequired(propName)) %}
*  @property {{ prop | toPHPType | safe }}{% if canBeNull %}|null{% endif %} ${{ propName | safe }}{% if prop.description() %} - {{ prop.description() }}{% endif %}
{%- endfor %}
*/
class {{schemaName | camelCase | upperFirst}} extends BasePayload
{
    public function __construct($attributes = [])
    {
        {%- for propName, prop in schema.properties() %}{%if prop.type() === "object" %}
        $attributes['{{propName}}'] = isset($attributes['{{propName}}']) ? new {{ prop | toPHPType | safe }}($attributes['{{propName}}']) : null;{% endif %}{%- endfor %}

        {%- for propName, prop in schema.properties() %}{%if prop | isDateOrDateTime %}
        $attributes['{{propName}}'] = isset($attributes['{{propName}}']) ? new DateTime($attributes['{{propName}}']) : null;{% endif %}{%- endfor %}

        parent::__construct($attributes);
    }
}
