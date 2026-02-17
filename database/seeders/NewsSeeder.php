<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\ContentBlock;
use App\Models\User;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $news1 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Нові технології в освіті 2026 року',
            'image' => 'news/tech-education.jpg',
            'short_description' => 'Огляд найсучасніших технологічних рішень, які змінюють освітній процес у навчальних закладах України.',
            'is_published' => true,
            'published_at' => now()->subDays(5),
        ]);

        $news1->contentBlocks()->createMany([
            [
                'type' => 'text',
                'text_content' => 'Цифрова трансформація освіти стала одним із пріоритетних напрямків розвитку освітньої системи України. Впровадження інноваційних технологій дозволяє покращити якість навчання та зробити його більш доступним.',
                'image_url' => null,
                'order' => 1,
            ],
            [
                'type' => 'text_image_right',
                'text_content' => 'Інтерактивні дошки, планшети та спеціалізоване програмне забезпечення стали невід\'ємною частиною сучасного класу. Вчителі активно використовують онлайн-платформи для створення інтерактивних уроків.',
                'image_url' => 'content/interactive-board.jpg',
                'order' => 2,
            ],
            [
                'type' => 'text_image_left',
                'text_content' => 'Штучний інтелект та машинне навчання відкривають нові можливості для персоналізації освітнього процесу. Системи адаптивного навчання аналізують прогрес кожного учня та пропонують індивідуальні завдання.',
                'image_url' => 'content/ai-learning.jpg',
                'order' => 3,
            ],
        ]);

        $news2 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Економічний розвиток регіонів',
            'image' => 'news/economy.jpg',
            'short_description' => 'Аналіз економічних показників та перспектив розвитку регіонів України у 2026 році.',
            'is_published' => true,
            'published_at' => now()->subDays(3),
        ]);

        $news2->contentBlocks()->createMany([
            [
                'type' => 'text',
                'text_content' => 'Регіональна економіка України демонструє позитивну динаміку розвитку. Експерти відзначають зростання інвестиційної активності та покращення бізнес-клімату.',
                'image_url' => null,
                'order' => 1,
            ],
            [
                'type' => 'image',
                'text_content' => null,
                'image_url' => 'content/economy-chart.jpg',
                'order' => 2,
            ],
            [
                'type' => 'text',
                'text_content' => 'Малий та середній бізнес відіграє ключову роль у розвитку місцевих економік. Державні програми підтримки підприємництва сприяють створенню нових робочих місць.',
                'image_url' => null,
                'order' => 3,
            ],
        ]);

        $news3 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Спортивні досягнення українських атлетів',
            'image' => 'news/sports.jpg',
            'short_description' => 'Огляд найяскравіших перемог та досягнень українських спортсменів на міжнародній арені.',
            'is_published' => true,
            'published_at' => now()->subDays(2),
        ]);

        $news3->contentBlocks()->createMany([
            [
                'type' => 'text_image_right',
                'text_content' => 'Українські спортсмени продовжують прославляти країну на міжнародних змаганнях. Цього місяця наші атлети здобули низку важливих перемог у різних видах спорту.',
                'image_url' => 'content/athletes.jpg',
                'order' => 1,
            ],
            [
                'type' => 'text',
                'text_content' => 'Особливо відзначилися представники легкої атлетики та плавання. Їхня наполегливість та професіоналізм стали прикладом для молодого покоління спортсменів.',
                'image_url' => null,
                'order' => 2,
            ],
        ]);

        $news4 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Культурні події лютого',
            'image' => 'news/culture.jpg',
            'short_description' => 'Найцікавіші культурні події, виставки та концерти, які відбуваються цього місяця.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $news4->contentBlocks()->createMany([
            [
                'type' => 'text',
                'text_content' => 'Лютий 2026 року багатий на культурні події. Музеї та галереї пропонують відвідувачам цікаві виставки сучасного мистецтва.',
                'image_url' => null,
                'order' => 1,
            ],
            [
                'type' => 'text_image_left',
                'text_content' => 'Національна опера представляє нову постановку класичного балету. Це унікальна можливість побачити видатних артистів на сцені головного театру країни.',
                'image_url' => 'content/opera.jpg',
                'order' => 2,
            ],
            [
                'type' => 'image',
                'text_content' => null,
                'image_url' => 'content/exhibition.jpg',
                'order' => 3,
            ],
        ]);

        $news5 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Інновації у медицині',
            'image' => 'news/medicine.jpg',
            'short_description' => 'Нові методи лікування та медичні технології, що впроваджуються в українських клініках.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $news5->contentBlocks()->createMany([
            [
                'type' => 'text',
                'text_content' => 'Медичні заклади України активно впроваджують сучасні технології діагностики та лікування. Телемедицина стає все більш доступною для пацієнтів з різних регіонів.',
                'image_url' => null,
                'order' => 1,
            ],
            [
                'type' => 'text_image_right',
                'text_content' => 'Роботизована хірургія дозволяє проводити складні операції з максимальною точністю та мінімальною травматичністю для пацієнтів.',
                'image_url' => 'content/robotic-surgery.jpg',
                'order' => 2,
            ],
            [
                'type' => 'text_image_left',
                'text_content' => 'Штучний інтелект допомагає лікарям швидше та точніше встановлювати діагнози. Системи аналізу медичних зображень виявляють патології на ранніх стадіях.',
                'image_url' => 'content/ai-diagnostics.jpg',
                'order' => 3,
            ],
        ]);

        News::create([
            'user_id' => $users->random()->id,
            'title' => 'Майбутнє штучного інтелекту',
            'image' => null,
            'short_description' => 'Прогнози експертів щодо розвитку AI технологій у найближчі роки.',
            'is_published' => false,
            'published_at' => null,
        ]);

        $news7 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Екологічні ініціативи міст України',
            'image' => 'news/ecology.jpg',
            'short_description' => 'Як українські міста впроваджують екологічні проєкти для покращення довкілля.',
            'is_published' => true,
            'published_at' => now()->subDays(7),
        ]);

        $news7->contentBlocks()->createMany([
            [
                'type' => 'text',
                'text_content' => 'Екологічна свідомість українців зростає з кожним роком. Міста запроваджують програми роздільного збору сміття та розвивають мережу велодоріжок.',
                'image_url' => null,
                'order' => 1,
            ],
            [
                'type' => 'image',
                'text_content' => null,
                'image_url' => 'content/green-city.jpg',
                'order' => 2,
            ],
        ]);

        $news8 = News::create([
            'user_id' => $users->random()->id,
            'title' => 'Цифрова трансформація бізнесу',
            'image' => 'news/digital-business.jpg',
            'short_description' => 'Як українські компанії адаптуються до цифрової епохи та впроваджують сучасні IT-рішення.',
            'is_published' => true,
            'published_at' => now()->subDays(4),
        ]);

        $news8->contentBlocks()->createMany([
            [
                'type' => 'text_image_right',
                'text_content' => 'Цифровізація бізнес-процесів стала необхідністю для конкурентоспроможності компаній. Автоматизація рутинних операцій звільняє ресурси для інновацій.',
                'image_url' => 'content/digital-transformation.jpg',
                'order' => 1,
            ],
            [
                'type' => 'text',
                'text_content' => 'Хмарні технології та системи управління дозволяють ефективно координувати роботу розподілених команд. Віддалена робота стає новою нормою для багатьох індустрій.',
                'image_url' => null,
                'order' => 2,
            ],
        ]);
    }
}
