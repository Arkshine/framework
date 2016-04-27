<?php

namespace Themosis\Hook;

use Themosis\Foundation\Application;

abstract class Hook implements IHook
{
    /**
     * The service container.
     *
     * @var \Themosis\Foundation\Application
     */
    protected $container;

    /**
     * List of registered actions.
     *
     * @var array
     */
    protected $events = [];

    public function __construct(Application $container)
    {
        $this->container = $container;
    }

    /**
     * Return the callback registered with the action hook.
     *
     * @param string $hook The action hook.
     *
     * @return string|\Closure|bool
     */
    public function getCallback($hook)
    {
        if (array_key_exists($hook, $this->events)) {
            return $this->events[$hook];
        }

        return false;
    }

    /**
     * Add an event for the specified hook.
     *
     * @param string          $hook
     * @param \Closure|string $callback      If string use this syntax "Class@method"
     * @param int             $priority      The priority order
     * @param int             $accepted_args The default number of accepted arguments
     *
     * @return \Closure|array|string
     */
    protected function addActionEvent($hook, $callback, $priority, $accepted_args)
    {
        // Check if $callback is a closure.
        if ($callback instanceof \Closure || is_array($callback)) {
            $this->addEventListener($hook, $callback, $priority, $accepted_args);
        } elseif (is_string($callback)) {
            if (is_callable($callback)) {
                // Used as a classic callback function.
                $this->addEventListener($hook, $callback, $priority, $accepted_args);
            } else {
                // Return the class responsible to handle the action.
                $callback = $this->addClassEvent($hook, $callback, $priority, $accepted_args);
            }
        }

        return $callback;
    }

    /**
     * Prepare the action callback for use in a class method.
     *
     * @param string $hook
     * @param string $class
     * @param int    $priority
     * @param int    $accepted_args
     *
     * @return array
     */
    protected function addClassEvent($hook, $class, $priority, $accepted_args)
    {
        $callback = $this->buildClassEventCallback($class, $hook);

        $this->addEventListener($hook, $callback, $priority, $accepted_args);

        return $callback;
    }

    /**
     * Build the array in order to call a class method.
     *
     * @param string $class
     * @param string $hook
     *
     * @return array
     */
    protected function buildClassEventCallback($class, $hook)
    {
        list($class, $method) = $this->parseClassEvent($class, $hook);

        $instance = $this->container->make($class);

        return [$instance, $method];
    }

    /**
     * Parse a class name and returns its name and its method.
     *
     * @param string $class
     * @param string $hook
     *
     * @return array
     */
    protected function parseClassEvent($class, $hook)
    {
        if (str_contains($class, '@')) {
            return explode('@', $class);
        }

        // If no method is defined, use the hook name as the method name.
        $method = str_contains($hook, '-') ? str_replace('-', '_', $hook) : $hook;

        return [$class, $method];
    }

    /**
     * Add an action event for the specified hook.
     *
     * @param string          $name
     * @param \Closure|string $callback
     * @param int             $priority
     * @param int             $accepted_args
     */
    protected function addEventListener($name, $callback, $priority, $accepted_args)
    {
        $this->events[$name] = [$callback, $priority, $accepted_args];
        add_action($name, $callback, $priority, $accepted_args);
    }
}
