ltiprovider already does all the clever stuff to enable a module instance as an LTI tool.
What we want to do is to provide a single launch URL for each Moodle tool *type*, which
will:

* Examine the context from the LTI request and see if a course for that context exists,
  creating it if not (ltiprovider takes care of creating and enrolling the user)
* Examine the resource_link_id from the LTI request and see if a module instance
  has already been created for this resource_link_id in this context
* If so, pass control onto ltiprovider to do the actual launch
* Otherwise, check the roles from the LTI request to see if the user is allowed
  to create a new instance and show error message if not
* Create a new instance of the tool and show the user the configuration form