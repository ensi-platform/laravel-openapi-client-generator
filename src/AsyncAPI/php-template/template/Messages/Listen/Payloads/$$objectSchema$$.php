<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Payloads;

{%- for propName, prop in schema.properties() %}{%if prop | isEnum %}
use {{ params.packageName | safe }}\Messages\Listen\Enums\{{ prop | toPHPType | safe }};{% endif %}{%- endfor %}
use DateTime;


class {{schemaName | camelCase | upperFirst}} extends BasePayload
{
    protected array $dates = [{%- for propName, prop in schema.properties() %}{%if prop | isDateOrDateTime %}
        "{{propName}}",{% endif %}{%- endfor %}
    ];

    protected array $objects = [{%- for propName, prop in schema.properties() %}{%if prop.type() === "object" %}
        "{{propName}}" => {{ prop | toPHPType | safe }}::class,{% endif %}{%- endfor %}
    ];

    protected array $enums = [{%- for propName, prop in schema.properties() %}{%if prop | isEnum %}
        "{{propName}}" => {{ prop | toPHPType | safe }}::class,{% endif %}{%- endfor %}
    ];


    {%- for propName, prop in schema.properties() %}
    {%- set canBeNull = (prop | isNullableOrNotRequired(propName)) %}

    /**{% if prop.description() %}
    * {{ prop.description() }}{% endif %}
    * @return {{ prop | toPHPType | safe }}{% if canBeNull %}|null{% endif %}
    */
    public function get{{ propName | camelCase | upperFirst  }}(): {% if canBeNull %}?{% endif %}{{ prop | toPHPType | safe }}
    {
        return $this->get('{{ propName }}');
    }

    /**
    * @param {{ prop | toPHPType | safe }}{% if canBeNull %}|null{% endif %} $value{% if prop.description() %} {{ prop.description() }}{% endif %}
    * @return static
    */
    public function set{{ propName | camelCase | upperFirst  }}({% if canBeNull %}?{% endif %}{{ prop | toPHPType | safe }} $value): static
    {
        return $this->set('{{ propName }}', $value);
    }

    {%- endfor %}
}
