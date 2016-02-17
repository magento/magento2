// from jQuery tablesorter
ts.addParser({
    id: "currency",
    is: function(s) {
        return /^[£$€?.]/.test(s);
    },
});