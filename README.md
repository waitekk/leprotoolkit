# LeproToolkit

LeproToolkit это удобная обертка для работы с Лепрозорием.

## Пример работы

```php
$toolkit = new LeproToolkit(99999, '0af31fgqjirwgfoqjf5wiqjf3194jfqj');

$user  = $toolkit->getProfileById(29910);
$user2 = $toolkit->getProfileByUsername('waitekk'); // WIP

$activationCode = '35cm';

if($user->storyContains($activationCode))
{
    // do smth
}
```
Пример возвращаемого ответа
```php
LeproToolkit\Models\Profile Object
(
    [uid] => 29910
    [username] => waitekk
    [name] =>
    [gender] =>
    [regdate] => 12&nbsp;Июня&nbsp;2009
    [regdateTimestamp] => 1244825940
    [karma] => 448
    [rating] => 3184
    [postsCount] => 1
    [commentsCount] => 212
    [subSitesCount] =>
    [city] =>
    [country] =>
    [userpic] =>
    [invited] =>
    [invitedBy] => 23317
    [userstory] =>
    [voteweight] => 4
)
```
