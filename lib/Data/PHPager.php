<?php
namespace Data;

/*=head1 NAME
Data\PHPager

=head1 DESCRIPTION
A PHP class which encapsulates common pagination calculations.

=head1 DEPENDENCIES
None

=head1 Synopsis

 $things = array(1,2,3,4,5,6,7,8,9,0);
 $phpager = new Data\PHPager(array('total_entries' => sizeof($things),
                              'entries_per_page' => 3,
                              'current_page' => isset($_GET['page']) ? $_GET['page'] : null,
                        ));
 echo "Number of pages is " . $phpager->last_page();
 echo "Entries on this page " . join(', ', $phpager->splice( $things ) );

=head1 Constructor

 new Data\PHPager(<array()>);

The constructor takes a single named list as an argument. This named list can have the following keys:

=over
=item total_entries
The total number of items in the set. This is a required key.
=item entries_per_page
The number of items to display on each page. This is a required key.
=item current_page
The Current page which should be displayed. Defaults to 1 if none is provided.
=back

=head1 Object Methods

=cut*/
class PHPager implements \JsonSerializable {
    private $total_entries = 1;
    private $entries_per_page = 1;
    private $current_page = 1;

    function __construct($init){
        $this->total_entries = (int)$init['total_entries'];
        $this->entries_per_page = (int)$init['entries_per_page'];
        $this->current_page($init['current_page']);
    }

    //
    // public methods
    //
/*=head2 total_entries($entries=null)
Get/Set the total number of entries in the set.
=cut*/
    function total_entries($entries=null){
        if ($entries!==null){
            $this->total_entries = (int)$entries; // needs to be an int
            // This looks weird, but setting current page to it's current
            // value will fix it if it's no longer in range
            $this->current_page( $this->current_page() );
        }
        return $this->total_entries;
    }
/*=head2 entries_per_page($entries=null)
Get/Set the maximum number of items to appear on each page
=cut*/
    function entries_per_page($entries=null){
        if ($entries!==null){
            $this->entries_per_page = (int)$entries; // needs to be an int
            // This looks weird, but setting current page to it's current
            // value will fix it if it's no longer in range
            $this->current_page( $this->current_page() );
        }
        return $this->entries_per_page;
    }
/*=head2 current_page($page_no=null)
Get/Set the current page value
=cut*/
    function current_page($page_no=null){
        if ($page_no!==null){
            $page_no = (int)$page_no; // needs to be an int
            $this->current_page = $page_no > $this->last_page() ? $this->last_page() : (
                                    $page_no < 1 ? 1 : $page_no
                                  );
        }
        return $this->current_page;
    }
/*=head2 last_page()
Get the calculated last page given the total entries and the entries per page
=cut*/
    function last_page(){
        return ceil($this->total_entries() / $this->entries_per_page());
    }
/*=head2 entries_on_this_page()
Get the calculated number of entries on the current page.
=cut*/
    function entries_on_this_page() {
        return $this->current_page() != $this->last_page() ?
                 $this->entries_per_page() : $this->total_entries() % $this->entries_per_page();
    }
/*=head2 previous_page()
Returns the page number of the previous page to the current page. Returns null if there isn't one.
=cut*/
    function previous_page(){
        return $this->current_page() - 1 > 0 ? $this->current_page() - 1 : null;
    }
/*=head2 next_page()
Returns the page number of the next page in the set. Returns null if there isn't one.
=cut*/
    function next_page(){
        return $this->current_page() + 1 <= $this->last_page() ? $this->current_page() + 1 : null;
    }
/*=head2 first_index()
Returns the index (zero based) of the first item displayed on the current page
=cut*/
    function first_index(){
        return ($this->current_page() - 1) * $this->entries_per_page();
    }
/*=head2 last_index()
Returns the index (zero based) of the last item displayed on the current page
=cut*/
    function last_index(){
        return ($this->first_index() + $this->entries_per_page()) > $this->total_entries() ?
            $this->total_entries() - 1 : $this->first_index() + $this->entries_per_page() - 1;
    }
/*=head2 first_item()
returns the number (index plus one) of the first item displayed on the current page
=cut*/
    function first_item() {
        return $this->first_index() + 1;
    }
/*=head2 last_item()
returns the number (index plus one) of the last item displayed on the current page
=cut*/
    function last_item() {
        return $this->last_index() + 1;
    }
/*=head2 splice(<list>)
GIven a list of items, returns the subset which should be displayed on the current page
=cut*/
    function splice($entries) {
        return array_splice($entries, $this->first_index(), $this->entries_on_this_page());
    }

/*=head2 jsonSerialise()
Required as this class implements JsonSerializable.
TODO: if necessary, add another method to allow setting the verbosity of this serialisation
      to instead call as_hash_full()
=cut*/
    public function jsonSerialize(){
        return $this->as_hash();
    }
/*=head2 as_hash()
Returns the key properties of the object (total_entries, entries_per_page, and current_page) in a keyed list format
=cut*/
    function as_hash() {
        return array(
            'total_entries'     => $this->total_entries(),
            'entries_per_page'  => $this->entries_per_page(),
            'current_page'      => $this->current_page(),
        );
    }
/*=head2 as_json()
Returns the same key values as the as_hash() method, except in serialised JSON format
=cut*/
    function as_json() {
        return json_encode($this->as_hash());
    }
/*=head2 as_hash_full()
Returns all properties of the object in a keyed list
=cut*/
    function as_hash_full(){
        return array(
            'total_entries'         => $this->total_entries(),
            'entries_per_page'      => $this->entries_per_page(),
            'current_page'          => $this->current_page(),
            'last_page'             => $this->last_page(),
            'entries_on_this_page'  => $this->entries_on_this_page(),
            'previous_page'         => $this->previous_page(),
            'next_page'             => $this->next_page(),
            'first_index'           => $this->first_index(),
            'last_index'            => $this->last_index(),
            'first_item'            => $this->first_item(),
            'last_item'             => $this->last_item(),
        );
    }
/*=head2 as_json_full()
Returns the same key values as the as_hash_full() method, except in serialised JSON format
=cut*/
    function as_json_full() {
        return json_encode($this->as_hash_full());
    }
}
?>
