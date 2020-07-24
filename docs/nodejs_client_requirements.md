## Генерация для NodeJS

* Для генерации клиента запустить команду `php artisan openapi:generate-client-nodejs`
* После успешной генерации по пути `<output_dir_template>-js` будет лежать сгенерированный клиент
* При первоначальной генерации нужно настроить связь с удаленным репозиторием, при последующих генерациях этого не требуется, нужно только запушить изменения.

## Модуль Nest
Плагин умеет генерировать модуль для фреймворка nest, для подключения к nest нужно:
1. Зарегистрировать конфиг модуля, например:
```js
import { registerAs } from "@nestjs/config";

export default registerAs('customer-auth-client', () => ({
    uri: process.env.CUSTOMER_AUTH_HOST
}));
```
У конфига можно задать два параметра:
`uri` - базовый url для запросов. Обязателен
`options` - любые другие опиции которые поддерживает инстанс axios. Может быть undefined.
Имя, с которым регистрировать конфиг, берется из названия npm пакета: например, если имя пакета `@ensi/customer-auth-client`, то регистрировать нужно с именем `customer-auth-client`.

2. Модуль экспортирует все классы из директории api клиента, что бы эти классы были доступны в di nest, модуль нужно зарегистрировать стандартным способом:
```js
@Module({
    imports: [
        CustomerAuthClientModule // Импорт модуля
    ]
    ...
})
export class AppModule {}
```
После чего можно использовать классы апи сервисов через  di, например, если в клиенте есть сервисы UsersApi и TokensApi, то:
```js
@Controller()
export class AppController {
    constructor(
        private readonly usersService: UsersApi,
        private readonly tokenService: TokensApi
    ) {}

    @Get()
    async getUserById(): Promise<any> {
        const { data: user } = await this.usersService.getUserById(1);
        return user;
    }
}
```

