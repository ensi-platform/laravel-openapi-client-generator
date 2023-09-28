<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Payloads;

{% if schema.description() or schema.examples() %}/**{% for line in schema.description() | splitByLines %}
 * {{ line | safe}}{% endfor %}{% if schema.examples() %}
 * Examples: {{schema.examples() | examplesToString | safe}}{% endif %}
 */
{% endif %}class {{schemaName | camelCase | upperFirst}}Payload extends BasePayload
{
    protected array $dates = [{%- for propName, prop in schema.properties() %}{%if prop | isDateOrDateTime %}
        "{{propName}}",{% endif %}{%- endfor %}
    ];

    protected array $objects = [{%- for propName, prop in schema.properties() %}{%if prop.type() === "object" %}
        "{{propName}}" => "{{ prop | toPHPType | safe }}",{% endif %}{%- endfor %}
    ];

{%- for propName, prop in schema.properties() %}
    {%- set varName = propName | camelCase %}
    {%- set varOriginalName = propName %}
    {%- set className = propName | camelCase | upperFirst %}
    {%- set propType = (prop | toPHPType) %}
    {%- set canBeNull = (prop | isNullableOrNotRequired(propName)) %}

    {% if prop.description() or prop.examples()%}/**{% for line in prop.description() | splitByLines %}
     * {{ line | safe}}{% endfor %}{% if prop.examples() %}
     * Examples: {{ prop.examples() | examplesToString | safe }}{% endif %}
     */{% endif %}
    public function get{{ className }}(): {% if canBeNull %}?{% endif %}{{ propType | safe }}
    {
        return $this->getValue('{{ varOriginalName }}');
    }
{%- endfor %}
}
