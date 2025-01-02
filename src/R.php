<?php

declare(strict_types=1);

namespace Xtompie\Rensen;

class R
 {
    /** @var callable|null */
    private $compute;

    /** @var mixed Last computed value (cache). */
    private $value;

    /** @var int Identifier of the instance. */
    private $id;

    /** @var int Next available identifier. */
    private static int $nextId = 1;

    /**
     * @var array<int,array<int>> List of watchers. For a given source ID, it stores a list of IDs that are watching this source.
     */
    public static array $watchers = [];

    /**
     * @var array<int,array<int>> List of inputs. For a given effect ID, it stores a list of IDs that are sources of this effect.
     */
    public static array $inputs = [];

    /**
     * @var static[] Global stack of currently computed effects.
     */
    private static array $stack = [];

    /**
     * @var static[] Map of ID -> R instance.
     */
    private static array $instances = [];

    public function __construct(callable $compute)
    {
        $this->id = self::$nextId++;
        self::$instances[$this->id] = $this;
        $this->compute = $compute;
        $this->recompute();
    }

    public function __invoke(...$args): mixed
    {
        if (func_num_args() > 0) {
            $this->set($args[0]);
            return null;
        } else {
            return $this->get();
        }
    }

    /**
     * Removes the instance from the registry and clears watchers.
     */
    public static function destroy(R $r): void
    {
        unset(self::$watchers[$r->id]);
        unset(self::$inputs[$r->id]);
        unset(self::$instances[$r->id]);
        $r->compute = null;
        $r->value = null;
    }

    /**
     * Returns the current value; registers the current effect (if it is being computed) as a watcher.
     */
    private function get(): mixed
    {
        if (!empty(self::$stack)) {
            $current = end(self::$stack);
            self::$watchers[$this->id][] = $current->id;
            self::$inputs[$current->id][] = $this->id;
        }
        return $this->value;
    }

    /**
     * Changes the compute function and recomputes the value.
     */
    private function set(callable $value): void
    {
        $this->compute = $value;
        $this->recompute();
    }

    /**
     * Removes all previously registered sources (inputs)
     * for the current object.
     */
    private function clearDependencies(): void
    {
        if (isset(self::$inputs[$this->id])) {
            foreach (self::$inputs[$this->id] as $inputId) {
                self::$watchers[$inputId] = array_filter(
                    self::$watchers[$inputId] ?? [],
                    fn($watcherId) => $watcherId !== $this->id
                );
            }
            unset(self::$inputs[$this->id]);
        }
    }

    /**
     * Main method for recomputing the value and notifying watchers.
     */
    private function recompute(): void
    {
        $this->clearDependencies();
        self::$stack[] = $this;
        $newValue = ($this->compute)();
        if ($newValue !== $this->value) {
            $this->value = $newValue;
            $this->notifyWatchers();
        }
        array_pop(self::$stack);
    }

    /**
     * Notifies all watchers (i.e., all effects that are watching the current source) about the change.
     */
    private function notifyWatchers(): void
    {
        if (isset(self::$watchers[$this->id])) {
            foreach (self::$watchers[$this->id] as $watcherId) {
                $watcherObj = self::$instances[$watcherId] ?? null;
                if ($watcherObj) {
                    $watcherObj->recompute();
                }
            }
        }
    }
}

