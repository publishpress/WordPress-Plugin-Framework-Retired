const name = 'Josh Perez';
const element = <h1>Hello, {name}</h1>;

(function ($) {
    $(function () {
        ReactDOM.render(
            element,
            document.getElementById('allex-addons-container')
        );
    });
})(jQuery);
