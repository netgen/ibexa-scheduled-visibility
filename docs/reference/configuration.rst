Configuration
=============

.. contents::
    :depth: 1
    :local:

Default configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        enabled: false
        type: 'location'
        content_types:
            all: false
            allowed: []
        sections:
            visible:
                section_id: 0
            hidden:
                section_id: 0
        object_states:
            object_state_group_id: 0
            visible:
                object_state_id: 0
            hidden:
                object_state_id: 0

Enabling scheduled visibility
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to use Ibexa scheduled visibility, you have to enable it:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        enabled: true

Changing visibility handling method:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to change visibility handling method(tu stavi link),
you need to set it to either **'location'**, **'section'** or **'object_state'**:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        type: 'section'

Content type limitations
~~~~~~~~~~~~~~~~~~~~~~~~

In order to include all content types in scheduled visibility mechanism you need to enable it:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        content_types:
            all: true

In order to limit content types you need to disable previously mentioned setting for all content types
and enter content types to be included in scheduled visibility mechanism:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        content_types:
            all: false
            allowed: ['content_type_1', 'content_type_2']

.. _section_configuration:

Section visibility handling method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If 'section' has been chosen as preferred visibility handling method,
ids of sections used for visible and hidden content need to be configured:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        type: 'section'
        sections:
            visible:
                section_id: 1
            hidden:
                section_id: 2

.. _object_state_configuration:

Object state visibility handling method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If 'object_state' has been chosen as preferred visibility handling method,
ids of object states used for visible and hidden content need to be configured,
as well as object state group id in which both of these states are:

.. code-block:: yaml

    netgen_ibexa_scheduled_visibility:
        type: 'object_state'
        object_states:
            object_state_group_id: 1
            visible:
                object_state_id: 1
            hidden:
                object_state_id: 2

.. note::

    Both object states must be in the configured object state group.
