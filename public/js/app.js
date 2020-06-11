var $collectionHolder;

var $addNewItem = $('<a href="#" class="btn btn-info">Add new item</a>')

$(document).ready(function () {
  // get the collectionHolder
  $collectionHolder = $('#exp_list');

  // append the add new item to the collectionHolder
  $collectionHolder.append($addNewItem);

  $collectionHolder.data('index', $collectionHolder.find('.card').length)

  // add remove button to existing items
  $collectionHolder.find('.card').each(function (item) {
    addRemoveButton($(this));
  });

  // handle the click event for addNewItem
  $addNewItem.click(function (e) {
    e.preventDefault();
    // create a new form and append it to the collectionHolder
    addNewForm();
  });

});

function addNewForm() {
  // getting the prototype
  var prototype = $collectionHolder.data('prototype');

  // create the index
  var index = $collectionHolder.data('index');

  // create the form
  var newForm = prototype;

  newForm = newForm.replace(/__name__/g, index);

  $collectionHolder.data('index', index+1);

  // create the card
  var $card = $('<div class="card"><div class="card-header bg-warning"></div></div>');

  // create the card-body and append the form to it
  var $cardBody = $('<div class="card-body bg-light"></div>').append(newForm);

  //append the body to the card
  $card.append($cardBody);

  //append the removeButton to the new card
  addRemoveButton($card);

  // append the card to the addNewItem
  $addNewItem.before($card);

}

// add new items (exp forms)


// remove them

function addRemoveButton ($card) {
  // create remove button
  var $removeButton = $('<a href="#" class="btn btn-danger">Remove</a>')

  //appending the removeButton to the card footer
  var $cardFooter = $('<div class="card-footer"></div>').append($removeButton);

  // handle the click event of the remove button
  $removeButton.click(function (e) {
    e.preventDefault();
    $(e.target).parents('.card').slideUp(1000, function () {
      $(this).remove();
    });
  });

  // append the footer to the card
  $card.append($cardFooter);

}