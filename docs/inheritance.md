# Inheritance with Aggregate Roots

If you want to make inheritance work with aggregate roots using a common repository for all subtypes, this can also be achieved very easily. 

## An example

Consider the following use case:

```php
abstract class User extends \Prooph\EventSourcing\AggregateRoot
{
    protected $name;
    
    protected $email;
    
    public function name(): string
    {
        return $this->name;
    }
    
    public function email(): string
    {
        return $this->email;
    }
    
    protected function apply(AggregateChanged $e): void
    {
        if ($e instanceof UserWasRegisterd) {
            $this->name = $e->name();
            $this->email = $e->email();
        }
    }
}

class Admin extends User
{
    public static function register(string $name, string $email): Admin
    {
        $self = new self();
        $self->recordThat(UserWasRegisterd::withData('admin', $name, $email);
        
        return $self;
    }
}

class Member extends User
{
    public static function register(string $name, string $email): Member
    {
        $self = new self();
        $self->recordThat(UserWasRegisterd::withData('member', $name, $email);
        
        return $self;
    }
}
```

So in order to make this work, you need 3 small changes in your application.

## Step 1: Create a UserAggregateTranslator

```php
final class UserAggregateTranslator extends \Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator
{
    /**
     * @param \Prooph\EventStore\Aggregate\AggregateType $aggregateType
     * @param \Iterator $historyEvents
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(
        \Prooph\EventStore\Aggregate\AggregateType $aggregateType, 
        \Iterator $historyEvents
    ) {
        $aggregateRootDecorator = $this->getAggregateRootDecorator();
        
        $firstEvent = $historyEvents->current();
        $type = $firstEvent->type();
        
        if ($type === 'admin') {
            return $aggregateRootDecorator->fromHistory(Admin::class, $historyEvents);
        } elseif ($type === 'member') {
            return $aggregateRootDecorator->fromHistory(Member::class, $historyEvents);
        }
    }
}
```

## Step 2: Change the assertion method in the EventStoreUserCollection

```php
final class EventStoreUserCollection extends 
    \Prooph\EventStore\Aggregate\AggregateRepository
{
    public function save(User $user): void
    {
        $this->saveAggregateRoot($user);
    }
    public function get(UserId $userId): ?User
    {
        return $this->getAggregateRoot($userId->toString());
    }
    protected function assertAggregateType($eventSourcedAggregateRoot)
    {
        \Assert\Assertion::isInstanceOf($eventSourcedAggregateRoot, User::class);
    }
}
```

## Step 3: Make use of your custom AggregateTranslator

```php
final class EventStoreUserCollectionFactory
{
    public function __invoke(ContainerInterface $container): EventStoreUserCollection
    {
        return new EventStoreUserCollection(
            $container->get(EventStore::class),
            AggregateType::fromAggregateRootClass(User::class),
            new UserAggregateTranslator()
        );
    }
}
```

If you use the provided container factory (\Prooph\EventStore\Container\Aggregate\AbstractAggregateRepositoryFactory)
then you can also just change the `aggregate_translator` key in your config to point to the new `UserAggregateTranslator`
and register the `UserAggregateTranslator` in your container.

see also: http://www.sasaprolic.com/2016/02/inheritance-with-aggregate-roots-in.html
