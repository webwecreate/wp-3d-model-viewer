<?php
/**
 * Hook Loader — registers all actions and filters for the plugin.
 *
 * Maintains lists of hooks and executes them at WordPress runtime.
 *
 * @package    WP3DModelViewer
 * @subpackage WP3DModelViewer/includes
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP3DMV_Loader {

    /**
     * Registered action hooks.
     *
     * @var array
     */
    protected $actions = array();

    /**
     * Registered filter hooks.
     *
     * @var array
     */
    protected $filters = array();

    // ─── Public API ────────────────────────────────────────────────────────────

    /**
     * Add an action to the collection.
     *
     * @param string $hook          The hook name.
     * @param object $component     Object instance that $callback belongs to.
     * @param string $callback      Method name.
     * @param int    $priority      Hook priority.
     * @param int    $accepted_args Number of arguments the callback accepts.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Add a filter to the collection.
     *
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int    $priority
     * @param int    $accepted_args
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Execute all registered hooks with WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ( $this->actions as $hook ) {
            add_action(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Build a hook entry array.
     *
     * @param array  $hooks
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int    $priority
     * @param int    $accepted_args
     * @return array
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
        return $hooks;
    }
}
