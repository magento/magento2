Magento\Persistent module enables set customer a long-term cookie containing internal id (random hash - to exclude brute
force) of persistent session. Persistent session data is kept in DB - so it's not deleted in some days and is kept for
as much time as we need. DB session keeps customerId + some data from real customer session that we want to sync (e.g.
num items in shopping cart). For registered customer this info is synced to persistent session if choose "Remember me"
checkbox during first login.
